<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel;

use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\ConstantRegistryInterface;
use Magento\Framework\DB\Select;

class Rewards extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_rewards_rewards';

    /**
     * Statistics columns
     */
    public const REWARDED = 'rewarded';
    public const REDEEMED = 'redeemed';
    public const EXPIRED = 'expired';
    public const PERIOD = 'period';
    public const BALANCE = 'balance';

    /**
     * Period unit for graph
     */
    public const HOUR = 1;
    public const DAY = 2;

    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getIgnoreRecalculate()) {
            return parent::_afterSave($object);
        }

        $connection = $this->getConnection();
        $rewardTable = $this->getTable(self::TABLE_NAME);
        $select = $connection->select()
            ->from(
                $rewardTable,
                'SUM(amount)'
            )
            ->where('customer_id = :customer_id');

        $pointsLeft = $connection->fetchOne(
            $select,
            [
                ':customer_id' => $object->getCustomerId()
            ]
        );

        $this->getConnection()->update(
            $this->getTable(self::TABLE_NAME),
            ['points_left' => $pointsLeft],
            ['id = ?' => $object->getId()]
        );

        return parent::_afterSave($object);
    }

    /**
     * @param int $customerId
     *
     * @return array
     */
    public function getStatistic($customerId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(self::TABLE_NAME),
            [
                self::REWARDED => 'SUM(IF(amount >= 0, amount, 0))',
                self::REDEEMED => 'ABS(SUM(IF(amount < 0 AND action != \''
                    . Actions::REWARDS_EXPIRED_ACTION . '\', amount, 0)))',
                self::EXPIRED => 'ABS(SUM(IF(amount < 0 AND action = \''
                    . Actions::REWARDS_EXPIRED_ACTION . '\', amount, 0)))',
            ]
        )->where(
            'customer_id = ?',
            (int)$customerId
        );

        $result = $this->getConnection()->fetchRow($select);
        $result[self::BALANCE] = round($result[self::REWARDED] - $result[self::REDEEMED] - $result[self::EXPIRED], 2);
        $result[self::REWARDED] = round($result[self::REWARDED] ?? 0, 2);
        $result[self::EXPIRED] = round($result[self::EXPIRED] ?? 0, 2);
        $result[self::REDEEMED] = round($result[self::REDEEMED] ?? 0, 2);

        return $result;
    }

    /**
     * @param $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRewards($customerId)
    {
        if (!$customerId) {
            return [];
        }

        $connection = $this->getConnection();
        $table = $this->getMainTable();

        $select = $connection->select()
            ->from(['main_table' => $table])
            ->reset(Select::COLUMNS)
            ->columns(['customer_id', 'amount' => 'points_left'])
            ->where('main_table.`customer_id` = ?', $customerId)
            ->limit(1)
            ->order('main_table.id DESC');

        return $connection->fetchAll($select);
    }

    /**
     * Set filter to load statistics data
     *
     * @param int|null $website
     * @param int|null $customerGroup
     * @param string|null $fromDate
     * @param string|null $toDate
     *
     * @return \Magento\Framework\DB\Select
     */
    public function addParamsFilter($website = null, $customerGroup = null, $fromDate = null, $toDate = null)
    {
        $table = $this->getMainTable();
        $select = $this->getConnection()->select()
            ->from(['main_table' => $table]);
        $joinCustomers = false;

        if ($website) {
            $select->where('customers.website_id =?', $website);
            $joinCustomers = true;
        }

        if ($customerGroup) {
            $select->where('customers.group_id =?', $customerGroup);
            $joinCustomers = true;
        }

        if ($joinCustomers) {
            $select->joinLeft(
                ['customers' => $this->getTable('customer_entity')],
                'main_table.customer_id = customers.entity_id',
                null
            );
        }

        if ($fromDate && $toDate) {
            $select->where('main_table.action_date BETWEEN \'' . $fromDate . '\' AND \'' . $toDate . '\'');
        }

        return $select;
    }

    /**
     * Return total information about rewards by params
     *
     * @param \Magento\Framework\DB\Select $select
     *
     * @return array
     */
    public function getTotalDataByParams($select)
    {
        $connection = $this->getConnection();

        $select
            ->reset(Select::COLUMNS)
            ->columns([
                self::REWARDED => 'SUM(IF(main_table.amount >= 0, main_table.amount, 0))',
                // Use action code in database
                self::REDEEMED => 'ABS(SUM(IF(main_table.amount < 0 AND main_table.action != \''
                    . Actions::REWARDS_EXPIRED_ACTION . '\', ' . 'main_table.amount, 0)))',
                // Use action code in database
                self::EXPIRED => 'ABS(SUM(IF(main_table.amount < 0 AND main_table.action =\''
                    . Actions::REWARDS_EXPIRED_ACTION . '\', ' . 'main_table.amount, 0)))',
            ]);

        return $connection->fetchRow($select);
    }

    /**
     * Return total information about rewards by params
     *
     * @param \Magento\Framework\DB\Select $select
     *
     * @return string
     */
    public function getCustomersCount($select)
    {
        $connection = $this->getConnection();

        $select
            ->reset(Select::COLUMNS)
            ->columns([
                'COUNT(DISTINCT main_table.customer_id)',
            ]);

        return $connection->fetchOne($select);
    }

    /**
     * Return Average Redeemed Points per Order
     *
     * @param \Magento\Framework\DB\Select $select
     *
     * @return float
     */
    public function getAverageOrderAmount($select)
    {
        $connection = $this->getConnection();

        $select
            ->reset(Select::COLUMNS)
            ->columns([
                'summary' => 'ABS(SUM(main_table.amount))',
                'count_order' => 'COUNT(main_table.amount)'
            ])
            ->where('main_table.action = ?', 'Order Paid') // Use action code in database
            ->group('main_table.action');

        $result = $connection->fetchRow($select);

        if (isset($result['count_order']) && $result['count_order']) {
            return round($result['summary'] / $result['count_order'], 2);
        }

        return 0;
    }

    /**
     * Return Average Redeemed Points per Order
     *
     * @param \Magento\Framework\DB\Select $select
     * @param int $periodUnit
     *
     * @return array
     */
    public function getGraphData($select, $periodUnit = self::DAY)
    {
        $connection = $this->getConnection();

        if ($periodUnit == self::HOUR) {
            $timeFormat = '%Y-%m-%d %H:00';
        } else {
            $timeFormat = '%Y-%m-%d';
        }

        $select
            ->reset(Select::COLUMNS)
            ->columns([
                self::PERIOD => 'DATE_FORMAT(main_table.action_date, \'' . $timeFormat . '\')',
                self::REWARDED => 'SUM(IF(main_table.amount >= 0, main_table.amount, 0))',
                // Use action code in database
                self::REDEEMED => 'ABS(SUM(IF(main_table.amount < 0 AND main_table.action != \'Expiration\', '
                    . 'main_table.amount, 0)))'
            ])
            ->group('DATE_FORMAT(main_table.action_date, \'' . $timeFormat . '\')');

        return $connection->fetchAll($select);
    }

    /**
     * @param int $customerId
     * @return array
     */
    public function getNonExpireIds(int $customerId): array
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();

        $select = $connection->select()
            ->from($table, ['id'])
            ->where(RewardsInterface::EXPIRATION_DATE . ' IS NULL')
            ->where(RewardsInterface::CUSTOMER_ID . ' = ?', $customerId);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Fetch the expiration data grouped by date
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerExpirationData(int $customerId): array
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();

        $select = $connection->select()
            ->from(
                $table,
                [
                    RewardsInterface::AMOUNT => 'SUM(expiring_amount)',
                    RewardsInterface::EXPIRATION_DATE
                ]
            )->where(RewardsInterface::CUSTOMER_ID . ' = ?', $customerId)
            ->where(RewardsInterface::EXPIRATION_DATE . ' IS NOT NULL')
            ->group([RewardsInterface::EXPIRATION_DATE]);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param string $date
     * @return array
     */
    public function getSumExpirationToDate(string $date): array
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();

        $select = $connection->select()
            ->from($table, [RewardsInterface::CUSTOMER_ID, RewardsInterface::AMOUNT => 'SUM(expiring_amount)'])
            ->where(RewardsInterface::EXPIRATION_DATE . ' < ?', $date)
            ->orWhere(RewardsInterface::ACTION . ' = ?', Actions::REWARDS_EXPIRED_ACTION)
            ->group(RewardsInterface::CUSTOMER_ID);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Return customers that subscribed to receiving emails about points expiring.
     *
     * @return array
     */
    public function getAllSubscribedCustomers(): array
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();

        $select = $connection->select()->from(['main_table' => $table], null)
            ->joinLeft(
                ['customers' => $this->getTable('customer_entity')],
                'main_table.customer_id = customers.entity_id',
                null
            )->joinLeft(
                ['eav' => $this->getTable('eav_attribute')],
                'eav.attribute_code = \'' . ConstantRegistryInterface::NOTIFICATION_EXPIRE . '\'',
                null
            )->joinLeft(
                ['eav_value' => $this->getTable('customer_entity_int')],
                'eav_value.entity_id = customers.entity_id AND eav.attribute_id = eav_value.attribute_id',
                null
            )->distinct()->columns(
                [
                    'customer_id' => 'main_table.customer_id',
                    'store_id' => 'customers.store_id'
                ]
            )->where('eav_value.value = ?', 1);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param string $today
     * @param string $toDate
     * @param int[] $customerIds
     * @return array
     */
    public function getFilteredRows(string $today, string $toDate, array $customerIds): array
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();

        $select = $connection->select()->from(['main_table' => $table], null)
            ->joinLeft(
                ['customers' => $this->getTable('customer_entity')],
                'main_table.customer_id = customers.entity_id',
                null
            )->columns(
                [
                    'days_left' => 'DATEDIFF(main_table.expiration_date, \'' . $today . '\')',
                    'points' => 'SUM(main_table.expiring_amount)',
                    'customer_id' => 'main_table.customer_id',
                    'store_id' => 'customers.store_id',
                    'expiration_date' => 'main_table.expiration_date',
                    'earn_date' => 'main_table.action_date'
                ]
            )->order('main_table.expiration_date ASC')->where('main_table.expiration_date <= ?', $toDate)->where(
                'main_table.customer_id IN (' . implode(',', $customerIds) . ')'
            )->group(['main_table.expiration_date', 'main_table.customer_id']);

        return $this->getConnection()->fetchAll($select);
    }
}
