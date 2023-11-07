<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\Rewards\Model\ResourceModel\Rewards;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ExpirationDateMigration implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $rewardsTable = $this->resourceConnection->getTableName(Rewards::TABLE_NAME);
        $expirationTable = $this->resourceConnection->getTableName('amasty_rewards_expiration_date');
        $connection = $this->resourceConnection->getConnection();

        if ($connection->isTableExists($expirationTable)) {
            $select = $connection
                ->select()
                ->joinLeft(
                    ["expiration" => $expirationTable],
                    new Expression("expiration.entity_id = main_table.expiration_id"),
                    ["expiration_date" => "date", "expiring_amount" => "expiration.amount"]
                );
            $connection->query($connection->updateFromSelect($select, ["main_table" => $rewardsTable]));

            $connection->dropTable($this->resourceConnection->getTableName('amasty_rewards_expiration_date'));
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
