<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup\Patch\Schema;

use Amasty\Rewards\Model\ResourceModel\Rule;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class RuleProductsSkuDataMigrations implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $tableName = $this->moduleDataSetup->getTable(Rule::TABLE_NAME);

        $connection = $this->moduleDataSetup->getConnection();

        if ($connection->tableColumnExists($tableName, 'promo_skus')) {
            $rulesSelect = $connection
                ->select()
                ->from($tableName)
                ->where("promo_skus IS NOT NULL AND promo_skus != ''");

            $rows = $connection->fetchAll($rulesSelect);
            $changedData = [];
            foreach ($rows as $row) {
                if (!empty($row['promo_skus'])) {
                    $row['products_sku'] = $row['promo_skus'];
                    $row['action_for_earning'] = $row['grant_points_for_specific_products'] = 1;
                }
                $changedData[] = $row;
            }
            if (!empty($changedData)) {
                $connection->insertOnDuplicate($tableName, $changedData);
            }

            $connection->dropColumn(
                $this->moduleDataSetup->getTable(Rule::TABLE_NAME),
                'promo_skus'
            );
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
