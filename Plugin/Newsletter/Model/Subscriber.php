<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Newsletter\Model;

use Amasty\Rewards\Api\HistoryRepositoryInterface;
use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Api\RuleRepositoryInterface;
use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\Customer\NewCustomerRegistry;
use Amasty\Rewards\Model\Customer\SubscribeToNotificationsByDefault;
use Amasty\Rewards\Model\Quote\EarningChecker;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Newsletter\Model\Subscriber as SourceSubscriber;
use Magento\Store\Model\StoreManagerInterface;

class Subscriber
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var RewardsProviderInterface
     */
    private $rewardsProvider;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var EarningChecker
     */
    private $earningChecker;

    /**
     * @var SubscribeToNotificationsByDefault
     */
    private $subscribeToNotificationsByDefault;

    /**
     * @var NewCustomerRegistry
     */
    private $newCustomerFlag;

    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        HistoryRepositoryInterface $historyRepository,
        RewardsProviderInterface $rewardsProvider,
        RuleRepositoryInterface $ruleRepository,
        EarningChecker $earningChecker,
        Config $configProvider,
        NewCustomerRegistry $newCustomerFlag,
        SubscribeToNotificationsByDefault $subscribeToNotificationsByDefault
    ) {
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->historyRepository = $historyRepository;
        $this->rewardsProvider = $rewardsProvider;
        $this->ruleRepository = $ruleRepository;
        $this->earningChecker = $earningChecker;
        $this->configProvider = $configProvider;
        $this->newCustomerFlag = $newCustomerFlag;
        $this->subscribeToNotificationsByDefault = $subscribeToNotificationsByDefault;
    }

    /**
     * @see SourceSubscriber::save()
     *
     * @param SourceSubscriber $subject
     * @param SourceSubscriber $result
     * @return SourceSubscriber
     */
    public function afterSave(
        SourceSubscriber $subject,
        SourceSubscriber $result
    ): SourceSubscriber {
        if ($subject->getCustomerId()
            && $this->configProvider->isEnabled()
            && $subject->getSubscriberStatus() === SourceSubscriber::STATUS_SUBSCRIBED
            && !$this->earningChecker->isForbiddenEarningByCustomerStatus((int)$subject->getCustomerId())
        ) {
            $this->addRewardPoints($subject);
        }

        return $result;
    }

    /**
     * @param SourceSubscriber $subscriber
     */
    private function addRewardPoints(SourceSubscriber $subscriber): void
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        $customerId = $subscriber->getCustomerId();

        if (!$websiteId || !$customerId) {
            return;
        }

        $customer = $this->customerRepository->getById($customerId);
        $customerGroupId = $customer->getGroupId();

        // fix new customers group ID
        if ($customerGroupId === 0) {
            $customerGroupId = 1;
        }

        /** @var int[] $appliedActions */
        $appliedActions = $this->historyRepository->getAppliedActionsId($customerId);

        $rules = $this->ruleRepository->getRulesByAction(
            Actions::SUBSCRIPTION_ACTION,
            $websiteId,
            $customerGroupId
        );

        if ($this->newCustomerFlag->isNewCustomer($customer->getEmail())) {
            $this->subscribeToNotificationsByDefault->execute($customer);
        }

        foreach ($rules as $rule) {
            if (!isset($appliedActions[$rule->getId()])) {
                $this->rewardsProvider->addPointsByRule(
                    $rule,
                    (int)$customerId,
                    (int)$subscriber->getStoreId()
                );
            }
        }
    }
}
