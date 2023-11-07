<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel\CustomerBalance;

use Amasty\Rewards\Model\CustomerBalance;
use Amasty\Rewards\Model\ResourceModel\CustomerBalance as ResourceCustomerBalance;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(CustomerBalance::class, ResourceCustomerBalance::class);
    }
}
