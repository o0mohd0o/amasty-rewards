<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Customer;

use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\ConstantRegistryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;

class SubscribeToNotificationsByDefault
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerResource
     */
    private $customerResource;

    public function __construct(
        Config $configProvider,
        CustomerFactory $customerFactory,
        CustomerResource $customerResource
    ) {
        $this->configProvider = $configProvider;
        $this->customerFactory = $customerFactory;
        $this->customerResource = $customerResource;
    }

    /**
     * @param CustomerInterface $customer
     * @throws \Exception
     */
    public function execute(CustomerInterface $customer): void
    {
        $customerModel = $this->customerFactory->create();
        $this->customerResource->load($customerModel, $customer->getId());

        if ($this->configProvider->canSubscribeByDefaultToEarnNotifications()) {
            $customerModel->setData(ConstantRegistryInterface::NOTIFICATION_EARNING, true);
            $this->customerResource->saveAttribute(
                $customerModel,
                ConstantRegistryInterface::NOTIFICATION_EARNING
            );
        }

        if ($this->configProvider->canSubscribeByDefaultToExpireNotifications()) {
            $customerModel->setData(ConstantRegistryInterface::NOTIFICATION_EXPIRE, true);
            $this->customerResource->saveAttribute(
                $customerModel,
                ConstantRegistryInterface::NOTIFICATION_EXPIRE
            );
        }
    }
}
