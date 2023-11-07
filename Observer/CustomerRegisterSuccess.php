<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Observer;

use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\Customer\SubscribeToNotificationsByDefault;
use Magento\Customer\Api\Data\GroupInterface;

class CustomerRegisterSuccess implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amasty\Rewards\Api\HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var \Amasty\Rewards\Api\RewardsProviderInterface
     */
    private $rewardsProvider;

    /**
     * @var \Amasty\Rewards\Api\RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $configProvider;

    /**
     * @var SubscribeToNotificationsByDefault
     */
    private $subscribeToNotificationsByDefault;

    public function __construct(
        \Amasty\Rewards\Api\HistoryRepositoryInterface $historyRepository,
        \Amasty\Rewards\Api\RewardsProviderInterface $rewardsProvider,
        \Amasty\Rewards\Api\RuleRepositoryInterface $ruleRepository,
        \Amasty\Rewards\Model\Config $configProvider,
        SubscribeToNotificationsByDefault $subscribeToNotificationsByDefault
    ) {
        $this->historyRepository = $historyRepository;
        $this->rewardsProvider = $rewardsProvider;
        $this->ruleRepository = $ruleRepository;
        $this->configProvider = $configProvider;
        $this->subscribeToNotificationsByDefault = $subscribeToNotificationsByDefault;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }
        /**
         * @var $customer \Magento\Customer\Model\Data\Customer
         */
        $customer = $observer->getCustomer();

        /** @var int[] $appliedActions */
        $appliedActions = $this->historyRepository->getAppliedActionsId($customer->getId());

        $rules = $this->ruleRepository->getRulesByAction(
            Actions::REGISTRATION_ACTION,
            $customer->getWebsiteId(),
            GroupInterface::NOT_LOGGED_IN_ID
        );

        $this->subscribeToNotificationsByDefault->execute($customer);

        /** @var \Amasty\Rewards\Model\Rule $rule */
        foreach ($rules as $rule) {
            if (!isset($appliedActions[$rule->getId()])) {
                $this->rewardsProvider->addPointsByRule($rule, $customer->getId(), $customer->getStoreId());
            }
        }
    }
}
