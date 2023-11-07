<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$resourceCustomer = $objectManager->create(ResourceCustomer::class);
$customer = $objectManager->create(Customer::class);
$customerRegistry = $objectManager->get(CustomerRegistry::class);
$customer->setWebsiteId(1)
    ->setId(777)
    ->setEmail('customer@rewardsamasty.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setLastname('Smith')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1);
$customer->isObjectNew(true);
$resourceCustomer->save($customer);
$customerRegistry->remove($customer->getId());
