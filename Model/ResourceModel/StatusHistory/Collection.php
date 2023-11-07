<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel\StatusHistory;

use Amasty\Rewards\Model\ResourceModel\StatusHistory as ResourceStatusHistory;
use Amasty\Rewards\Model\StatusHistory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(StatusHistory::class, ResourceStatusHistory::class);
    }

    /**
     * @param int $customerId
     */
    public function addCustomerFilter(int $customerId): void
    {
        $this->addFieldToFilter('customer_id', $customerId);
    }
}
