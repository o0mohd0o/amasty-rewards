<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel;

use Amasty\Rewards\Api\Data\StatusHistoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StatusHistory extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rewards_status_history';

    public const EXCLUDE_ACTION = 1;
    public const RESTORE_ACTION = 0;

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'status_id');
    }

    /**
     * @param StatusHistoryInterface $entity
     * @param int $customerId
     * @param string $date
     * @return StatusHistoryInterface|null
     * @throws LocalizedException
     */
    public function loadByCustomerIdAndDate(
        StatusHistoryInterface $entity,
        int $customerId,
        string $date
    ): ?StatusHistoryInterface {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where('date < ?', $date)
            ->where('customer_id = ?', $customerId)
            ->order('date DESC');
        $data = $this->getConnection()->fetchRow($select);
        if ($data) {
            $entity->addData($data);

            return $entity;
        }

        return null;
    }
}
