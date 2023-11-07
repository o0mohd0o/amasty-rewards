<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup\Patch\Data;

use Amasty\Rewards\Model\Flag;
use Magento\Framework\DB\Select;
use Magento\Framework\FlagManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Api\Data\OrderInterface;

class SaveLastOrderNumber implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var FlagManager
     */
    private $flagManager;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        FlagManager $flagManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->flagManager = $flagManager;
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $tableName = $this->moduleDataSetup->getTable('sales_order');

        if (!$this->flagManager->getFlagData(Flag::OLD_ORDER_ID)) {
            $select = $this->moduleDataSetup->getConnection()
                ->select()
                ->from($tableName, [OrderInterface::ENTITY_ID])
                ->order(OrderInterface::ENTITY_ID . ' ' . Select::SQL_DESC);

            $orderId = $this->moduleDataSetup->getConnection()->fetchOne($select);

            $this->flagManager->saveFlag(Flag::OLD_ORDER_ID, (int)$orderId);
        }
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
