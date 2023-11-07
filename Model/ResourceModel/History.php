<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel;

use Amasty\Rewards\Api\Data\HistoryInterface;

class History extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_rewards_history';

    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, HistoryInterface::ID);
    }

    public function loadByCustomer($customerId, $action, $params = null)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where('customer_id=:customer_id')
            ->where('action=:action');

        if ($params) {
            $select->where(HistoryInterface::PARAMS . " LIKE ?", '%' . $params . '%');
        }

        $result = $this->getConnection()->fetchRow(
            $select,
            [
                'customer_id' => $customerId,
                'action' => $action
            ]
        );

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * Get all applied actions ID
     *
     * @param int $customerId
     * @param string|null $params
     *
     * @return array
     */
    public function getAppliedActions($customerId, $params = null)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->getMainTable()),
            ['action_id']
        )->where(
            'customer_id=:customer_id'
        );

        if ($params) {
            $select->where(HistoryInterface::PARAMS . " LIKE ?", '%' . $params . '%');
        }

        return $this->getConnection()
            ->fetchAll(
                $select,
                [
                    'customer_id' => $customerId
                ]
            );
    }

    /**
     * Get Last Year applied actions ID
     *
     * @param int $customerId
     * @param int $startDate
     * @param string|null $params
     *
     * @return array
     */
    public function getLastYearActions($customerId, $startDate, $params = null)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable($this->getMainTable()),
            ['action_id']
        )->where(
            'customer_id = :customer_id'
        )->where(
            new \Zend_Db_Expr(
                "DATE_FORMAT(`date`, '%Y-%m-%d') > '" . date('Y-m-d', strtotime($startDate . "-1 year")) . "'"
            )
        );

        if ($params) {
            $select->where(HistoryInterface::PARAMS . " LIKE ?", '%' . $params . '%');
        }

        return $this->getConnection()
            ->fetchAll(
                $select,
                ['customer_id' => $customerId]
            );
    }
}
