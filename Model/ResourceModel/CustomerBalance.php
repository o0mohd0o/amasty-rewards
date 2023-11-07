<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel;

use Amasty\Rewards\Api\Data\CustomerBalanceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerBalance extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rewards_customer_balance';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, CustomerBalanceInterface::ID);
    }
}
