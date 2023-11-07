<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Api\Data\StatusHistoryInterface;
use Magento\Framework\Model\AbstractModel;

class StatusHistory extends AbstractModel implements StatusHistoryInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\StatusHistory::class);
    }

    /**
     * @return int
     */
    public function getStatusId(): int
    {
        return (int)$this->getData(StatusHistoryInterface::STATUS_ID);
    }

    /**
     * @param int $statusId
     * @return StatusHistoryInterface
     */
    public function setStatusId(int $statusId): StatusHistoryInterface
    {
        $this->setData(StatusHistoryInterface::STATUS_ID, $statusId);

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int)$this->getData(StatusHistoryInterface::CUSTOMER_ID);
    }

    /**
     * @param int $customerId
     * @return StatusHistoryInterface
     */
    public function setCustomerId(int $customerId): StatusHistoryInterface
    {
        $this->setData(StatusHistoryInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->getData(StatusHistoryInterface::DATE);
    }

    /**
     * @param string $date
     * @return StatusHistoryInterface
     */
    public function setDate(string $date): StatusHistoryInterface
    {
        $this->setData(StatusHistoryInterface::DATE, $date);

        return $this;
    }

    /**
     * @return int
     */
    public function getAction(): int
    {
        return (int)$this->getData(StatusHistoryInterface::ACTION);
    }

    /**
     * @param int $action
     * @return StatusHistoryInterface
     */
    public function setAction(int $action): StatusHistoryInterface
    {
        $this->setData(StatusHistoryInterface::ACTION, $action);

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminName(): string
    {
        return $this->getData(StatusHistoryInterface::ADMIN_NAME);
    }

    /**
     * @param string $adminName
     * @return StatusHistoryInterface
     */
    public function setAdminName(string $adminName): StatusHistoryInterface
    {
        $this->setData(StatusHistoryInterface::ADMIN_NAME, $adminName);

        return $this;
    }
}
