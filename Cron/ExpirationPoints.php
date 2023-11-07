<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Cron;

use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\ConfigFactory;
use Amasty\Rewards\Model\ConstantRegistryInterface as Constant;
use Amasty\Rewards\Model\Date;
use Amasty\Rewards\Model\ResourceModel\Rewards;
use Amasty\Rewards\Model\TransportFactory;
use Magento\Store\Api\StoreRepositoryInterface;

class ExpirationPoints
{
    /**
     * @var Date
     */
    private $date;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var Rewards
     */
    private $rewardsResource;

    /**
     * @var TransportFactory
     */
    private $transportFactory;

    /**
     * @var RewardsProviderInterface
     */
    private $rewardsProvider;

    /**
     * @var RewardsRepositoryInterface
     */
    private $rewardsRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    public function __construct(
        Date $date,
        ConfigFactory $configFactory,
        Rewards $rewardsResource,
        TransportFactory $transportFactory,
        RewardsProviderInterface $rewardsProvider,
        RewardsRepositoryInterface $rewardsRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->date = $date;
        $this->configFactory = $configFactory;
        $this->rewardsResource = $rewardsResource;
        $this->transportFactory = $transportFactory;
        $this->rewardsProvider = $rewardsProvider;
        $this->rewardsRepository = $rewardsRepository;
        $this->storeRepository = $storeRepository;
    }

    public function execute(): void
    {
        $this->deductExpiredPoints();
        $this->checkExpiration();
    }

    /**
     * Deduct expired points from customer rewards balance
     */
    private function deductExpiredPoints()
    {
        $rows = $this->rewardsResource->getSumExpirationToDate($this->date->getDateWithOffsetByDays(0));

        foreach ($rows as $row) {
            if (!empty($row['amount']) && $row['amount'] > 0) {
                /** @var RewardsInterface $modelRewards */
                $modelRewards = $this->rewardsRepository->getEmptyModel();
                $modelRewards->setAmount((float)$row['amount']);
                $modelRewards->setCustomerId((int)$row['customer_id']);
                $modelRewards->setAction(Actions::REWARDS_EXPIRED_ACTION);
                $this->rewardsProvider->deductRewardPoints($modelRewards);
            }
        }
    }

    /**
     * Check expiration date by subscribed customers and stores
     */
    private function checkExpiration()
    {
        $rewardConfig = $this->configFactory->create();
        $storeDays = [];
        $maxDay = 0;

        foreach ($this->storeRepository->getList() as $store) {
            if (!$rewardConfig->getSendExpireNotification((string) $store->getId())) {
                continue;
            }

            $expireDaysToSend = $rewardConfig->getExpireDaysToSend((string) $store->getId());
            if (!$expireDaysToSend) {
                continue;
            }

            rsort($expireDaysToSend);
            $storeDays[(int) $store->getId()] = $expireDaysToSend;
            $maxDay = max($maxDay, max($expireDaysToSend));
        }

        $customerIds = $this->getCustomerIds($storeDays);
        if (empty($customerIds)) {
            return;
        }

        $expirationRows = $this->getExpirationRows($customerIds, $maxDay);

        $transport = $this->transportFactory->create();

        foreach ($expirationRows as $deadlines) {
            $storeId = (int) current($deadlines)[Constant::STORE_ID];
            foreach ($storeDays[$storeId] as $day) {
                if ($this->canSend($deadlines, $day)) {
                    $transport->sendExpireNotification($deadlines, $day);
                }
            }
        }
    }

    /**
     * Allows to only send emails on exact days specified in configuration.
     *
     * @param array[] $deadlines
     * @param int $day
     * @return bool
     */
    private function canSend(array $deadlines, int $day): bool
    {
        foreach ($deadlines as $deadline) {
            if ((int) $deadline['days_left'] === $day) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $storeDays
     * @return int[]
     */
    private function getCustomerIds(array $storeDays): array
    {
        $customers = $this->rewardsResource->getAllSubscribedCustomers();
        if (!$customers) {
            return [];
        }

        $selectedCustomerIds = [];
        foreach ($customers as $customer) {
            $storeId = (int) $customer[Constant::STORE_ID];
            if (isset($storeDays[$storeId])) {
                $selectedCustomerIds[] = $customer[RewardsInterface::CUSTOMER_ID];
            }
        }

        return $selectedCustomerIds;
    }

    /**
     * @param int[] $customerIds
     * @param int $maxDay
     * @return array<int, array[]>
     */
    private function getExpirationRows(array $customerIds, int $maxDay): array
    {
        $expirationRows = $this->rewardsResource->getFilteredRows(
            $this->date->getDateWithOffsetByDays(0),
            $this->date->getDateWithOffsetByDays($maxDay),
            $customerIds
        );

        if (!$expirationRows) {
            return [];
        }

        $rowsByCustomer = [];
        foreach ($expirationRows as &$expirationRow) {
            $rowsByCustomer[$expirationRow[RewardsInterface::CUSTOMER_ID]][] = $expirationRow;
        }

        return $rowsByCustomer;
    }
}
