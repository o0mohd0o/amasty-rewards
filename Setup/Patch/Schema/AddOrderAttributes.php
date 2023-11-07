<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup\Patch\Schema;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Api\Data\SalesQuote\OrderInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Setup\SalesSetupFactory;

class AddOrderAttributes implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $salesSetup = $this->salesSetupFactory->create(
            ['resourceName' => 'sales_setup', 'setup' => $this->moduleDataSetup]
        );

        $salesSetup->addAttribute(Order::ENTITY, EntityInterface::POINTS_EARN, [
            'type' => Table::TYPE_DECIMAL,
            'visible' => false,
            'nullable' => true
        ]);

        $salesSetup->addAttribute(Order::ENTITY, EntityInterface::POINTS_SPENT, [
            'type' => Table::TYPE_DECIMAL,
            'visible' => false,
            'nullable' => true
        ]);

        $salesSetup->addAttribute(Order::ENTITY, OrderInterface::POINTS_REFUND, [
            'type' => Table::TYPE_DECIMAL,
            'visible' => false,
            'nullable' => true
        ]);

        $salesSetup->addAttribute(Order::ENTITY, OrderInterface::POINTS_DEDUCT, [
            'type' => Table::TYPE_DECIMAL,
            'visible' => false,
            'nullable' => true
        ]);

        $salesSetup->addAttribute('order_item', EntityInterface::POINTS_EARN, [
            'type' => Table::TYPE_DECIMAL,
            'visible' => false,
            'nullable' => true
        ]);

        $salesSetup->addAttribute('order_item', EntityInterface::POINTS_SPENT, [
            'type' => Table::TYPE_DECIMAL,
            'visible' => false,
            'nullable' => true
        ]);
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
