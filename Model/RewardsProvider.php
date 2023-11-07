<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Api;
use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\ResourceModel\Rewards\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Provide data for Rewards
 */
class RewardsProvider implements Api\RewardsProviderInterface
{
    public const REWARD_ID = 'reward_id';
    public const REST_AMOUNT = 'rest_amount';
    public const EVERYTIME_VISIBLE_FOR_CUSTOMER = true;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var Api\HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var Api\RewardsRepositoryInterface
     */
    private $rewardsRepository;

    /**
     * @var CustomerBalanceRepositoryInterface
     */
    private $customerBalanceRepository;

    /**
     * @var CollectionFactory
     */
    private $rewardsCollectionFactory;

    public function __construct(
        Date $date,
        Config $config,
        Transport $transport,
        Api\HistoryRepositoryInterface $historyRepository,
        Api\RewardsRepositoryInterface $rewardsRepository,
        CustomerBalanceRepositoryInterface $customerBalanceRepository,
        CollectionFactory $rewardsCollectionFactory
    ) {
        $this->date = $date;
        $this->config = $config;
        $this->transport = $transport;
        $this->historyRepository = $historyRepository;
        $this->rewardsRepository = $rewardsRepository;
        $this->customerBalanceRepository = $customerBalanceRepository;
        $this->rewardsCollectionFactory = $rewardsCollectionFactory;
    }

    /**
     * @param Api\Data\RuleInterface $rule
     * @param int $customerId
     * @param int $storeId
     * @param null $amount
     * @param null $comment
     */
    public function addPointsByRule($rule, $customerId, $storeId, $amount = null, $comment = null)
    {
        /** @var RewardsInterface $modelRewards */
        $modelRewards = $this->rewardsRepository->getEmptyModel();
        $amount = $amount ?: $rule->getAmount();
        $modelRewards->setAmount($amount);
        $modelRewards->setComment($comment ?: (string)$rule->getStoreLabel((int)$storeId));

        $expirationDate = null;
        $expiringAmount = 0;
        if ($rule->getExpirationDays($storeId) !== null) {
            $expirationDate = $this->date->getDateWithOffsetByDays($rule->getExpirationDays($storeId));
            $expiringAmount = $amount;
        }

        $modelRewards->setExpirationDate($expirationDate);
        $modelRewards->setExpiringAmount($expiringAmount);
        $modelRewards->setAction($rule->getAction());
        $modelRewards->setCustomerId((int)$customerId);
        $this->addRewardPoints($modelRewards, (int)$storeId);

        /** @var Api\Data\HistoryInterface $historyRewards */
        $historyRewards = $this->historyRepository->getEmptyModel();
        $historyRewards->setCustomerId($customerId);
        $historyRewards->setActionId($rule->getRuleId());
        $historyRewards->setParams($rule->getParams());
        $this->historyRepository->save($historyRewards);
    }

    /**
     * @param RewardsInterface $modelRewards
     * @param int|null $storeId
     * @return void
     */
    public function addRewardPoints(RewardsInterface $modelRewards, ?int $storeId = null): void
    {
        if ($modelRewards->getVisibleForCustomer() === null) {
            $modelRewards->setVisibleForCustomer(self::EVERYTIME_VISIBLE_FOR_CUSTOMER);
        }
        $this->commitAction($modelRewards);

        $this->transport->sendRewardsEarningNotification(
            $modelRewards->getAmount(),
            $modelRewards->getCustomerId(),
            $modelRewards->getAction(),
            $storeId
        );
    }

    /**
     * @param RewardsInterface $modelRewards
     */
    public function deductRewardPoints(RewardsInterface $modelRewards): void
    {
        if ($modelRewards->getVisibleForCustomer() === null) {
            $modelRewards->setVisibleForCustomer(self::EVERYTIME_VISIBLE_FOR_CUSTOMER);
        }
        $this->deductValidation($modelRewards);
        $this->processDeductExpirationPoints($modelRewards);
        $modelRewards->setAmount(-$modelRewards->getAmount());
        $this->commitAction($modelRewards);
    }

    /**
     * Commit function for add or deduct rewards action
     * Which save rewards action into amasty_rewards_rewards table and set rewards amount in quote
     *
     * @param RewardsInterface $modelRewards
     * @return RewardsInterface
     */
    private function commitAction(RewardsInterface $modelRewards): RewardsInterface
    {
        $customerId = (int)$modelRewards->getCustomerId();
        $modelCustomerBalance = $this->customerBalanceRepository->getBalanceEntityByCustomerId($customerId);
        $currentBalance = $modelCustomerBalance->getBalance();
        $modelCustomerBalance->setCustomerId($customerId);

        if ($modelRewards->getAction() === Actions::ADMIN_ACTION) {
            $modelRewards->setAction($this->config->getAdminActionName() ?: __('Admin Point Change')->render());
        }
        $newBalance = $currentBalance + $modelRewards->getAmount();
        $modelRewards->setPointsLeft($newBalance);
        $modelCustomerBalance->setBalance($newBalance);
        $this->rewardsRepository->save($modelRewards);
        $this->customerBalanceRepository->save($modelCustomerBalance);

        return $modelRewards;
    }

    /**
     * @param RewardsInterface $modelRewards
     * @throws LocalizedException
     */
    private function deductValidation(RewardsInterface $modelRewards): void
    {
        $amount = $modelRewards->getAmount();
        $customerId = $modelRewards->getCustomerId();

        if ($amount <= 0) {
            throw new LocalizedException(__('You are trying to deduct negative or null amount of rewards point(s).'));
        }

        if (!$amount || !$customerId || !$modelRewards->getAction()) {
            throw new \InvalidArgumentException('Required parameter is not set.');
        }

        $currentBalance = $this->customerBalanceRepository->getBalanceByCustomerId($customerId);

        if ($amount > $currentBalance) {
            throw new LocalizedException(__('Too much point(s) used.'));
        }
    }

    /**
     * @param RewardsInterface $modelRewards
     * @throws LocalizedException
     */
    public function processDeductExpirationPoints(RewardsInterface $modelRewards): void
    {
        $usedRewards = $this->getUsedRewards($modelRewards->getAmount(), $modelRewards->getCustomerId());
        foreach ($usedRewards as $rewards) {
            $rewardEntity = $this->rewardsRepository->getById($rewards[self::REWARD_ID]);
            $rewardEntity->setIgnoreRecalculate(true);
            if (!$rewards[self::REST_AMOUNT]) {
                $rewardEntity->setExpiringAmount(0);
                $rewardEntity->setExpirationDate(null);
            } else {
                $rewardEntity->setExpiringAmount(max(0, (float)$rewards[self::REST_AMOUNT]));
            }
            $this->rewardsRepository->save($rewardEntity);
        }
    }

    /**
     * @param float $amount
     * @param int $customerId
     * @return array
     * @throws LocalizedException
     */
    private function getUsedRewards(float $amount, int $customerId): array
    {
        $usedRewards = [];
        $expirationRewards = $this->getPointsByCustomerId($customerId);
        /** @var \Amasty\Rewards\Model\Rewards $reward */
        foreach ($expirationRewards as $reward) {
            $amountToExpire = $reward->getExpiringAmount();
            if ($amountToExpire <= 0) {
                $amountToExpire = $reward->getAmount();
            }

            if ($amountToExpire >= $amount) {
                $usedRewards[] = [
                    self::REWARD_ID => $reward->getId(),
                    self::REST_AMOUNT => $reward->getExpiringAmount() - $amount
                ];
                $amount = 0;

                break;
            } else {
                $usedRewards[] = [
                    self::REWARD_ID => $reward->getId(),
                    self::REST_AMOUNT => 0
                ];

                $amount = round($amount - $amountToExpire, 2);
            }
        }

        if ($amount) {
            throw new LocalizedException(__('Reward points balance mismatch: please contact store administrator.'));
        }

        return $usedRewards;
    }

    /**
     * @param int $customerId
     * @return RewardsInterface[]
     */
    private function getPointsByCustomerId(int $customerId)
    {
        $rewardsCollection = $this->rewardsCollectionFactory->create();
        $rewardsCollection->getPointsByCustomerId($customerId);

        return $rewardsCollection->getItems();
    }
}
