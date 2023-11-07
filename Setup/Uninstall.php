<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup;

use Amasty\Rewards\Model\ResourceModel\CustomerBalance;
use Amasty\Rewards\Model\ResourceModel\History;
use Amasty\Rewards\Model\ResourceModel\Rewards;
use Amasty\Rewards\Model\ResourceModel\Rule;
use Amasty\Rewards\Model\ResourceModel\StatusHistory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @param SchemaSetupInterface $installer
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(SchemaSetupInterface $installer, ModuleContextInterface $context): void
    {
        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable(History::TABLE_NAME));
        $installer->getConnection()->dropTable($installer->getTable(StatusHistory::TABLE_NAME));
        $installer->getConnection()->dropTable($installer->getTable(Rewards::TABLE_NAME));
        $installer->getConnection()->dropTable($installer->getTable('amasty_rewards_quote'));
        $installer->getConnection()->dropTable($installer->getTable('amasty_rewards_rule_website'));
        $installer->getConnection()->dropTable($installer->getTable('amasty_rewards_rule_customer_group'));
        $installer->getConnection()->dropTable($installer->getTable(Rule::TABLE_NAME_LABEL));
        $installer->getConnection()->dropTable($installer->getTable(Rule::TABLE_NAME));
        $installer->getConnection()->dropTable($installer->getTable(CustomerBalance::TABLE_NAME));

        $installer->endSetup();
    }
}
