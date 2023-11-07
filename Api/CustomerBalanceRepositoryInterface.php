<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api;

use Amasty\Rewards\Api\Data\CustomerBalanceInterface;

interface CustomerBalanceRepositoryInterface
{
    /**
     * @param int $customerId
     * @return CustomerBalanceInterface
     */
    public function getBalanceEntityByCustomerId(int $customerId): CustomerBalanceInterface;

    /**
     * @param int $customerId
     * @return float
     */
    public function getBalanceByCustomerId(int $customerId): float;

    /**
     * @param CustomerBalanceInterface $entity
     * @return void
     */
    public function save(CustomerBalanceInterface $entity): void;
}
