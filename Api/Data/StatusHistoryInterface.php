<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api\Data;

interface StatusHistoryInterface
{
    public const STATUS_ID = 'status_id';
    public const CUSTOMER_ID = 'customer_id';
    public const DATE ='date';
    public const ACTION = 'action';
    public const ADMIN_NAME = 'admin_name';

    /**
     * @return int
     */
    public function getStatusId(): int;

    /**
     * @param int $statusId
     * @return StatusHistoryInterface
     */
    public function setStatusId(int $statusId): StatusHistoryInterface;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     * @return StatusHistoryInterface
     */
    public function setCustomerId(int $customerId): StatusHistoryInterface;

    /**
     * @return string
     */
    public function getDate(): string;

    /**
     * @param string $date
     * @return StatusHistoryInterface
     */
    public function setDate(string $date): StatusHistoryInterface;

    /**
     * @return int
     */
    public function getAction(): int;

    /**
     * @param int $action
     * @return StatusHistoryInterface
     */
    public function setAction(int $action): StatusHistoryInterface;

    /**
     * @return string
     */
    public function getAdminName(): string;

    /**
     * @param string $adminName
     * @return StatusHistoryInterface
     */
    public function setAdminName(string $adminName): StatusHistoryInterface;
}
