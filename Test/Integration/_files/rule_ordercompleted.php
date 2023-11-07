<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\Repository\RuleRepository;
use Amasty\Rewards\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$repository = $objectManager->create(RuleRepository::class);
$rule = $objectManager->create(Rule::class);
$rule->setId(777)
    ->setIsActive(1)
    ->setAction(Actions::ORDER_COMPLETED_ACTION)
    ->setAmount(10)
    ->setName('Test Rule')
    ->setCustomerGroupIds([1])
    ->setWebsiteIds([1]);
$repository->save($rule);
