<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api\Data;

interface CustomerBalanceInterface
{
    public const ID = 'id';
    public const CUSTOMER_ID = 'customer_id';
    public const BALANCE = 'balance';

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     * @return void
     */
    public function setCustomerId(int $customerId): void;

    /**
     * @return float
     */
    public function getBalance(): float;

    /**
     * @param float $balance
     * @return void
     */
    public function setBalance(float $balance): void;
}
