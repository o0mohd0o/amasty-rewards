<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Api\Data\CustomerBalanceInterface;
use Magento\Framework\Model\AbstractModel;

class CustomerBalance extends AbstractModel implements CustomerBalanceInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\CustomerBalance::class);
    }

    public function getCustomerId(): int
    {
        return (int)$this->getData(self::CUSTOMER_ID);
    }

    public function setCustomerId(int $customerId): void
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getBalance(): float
    {
        return (float)$this->getData(self::BALANCE);
    }

    public function setBalance(float $balance): void
    {
        $this->setData(self::BALANCE, $balance);
    }
}
