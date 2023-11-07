<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel\Rewards\Collection;

use Magento\Customer\Controller\RegistryConstants;

class RewardsHistoryGrid extends \Amasty\Rewards\Model\ResourceModel\Rewards\Collection
{
    /**
     * Initialize db select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $customerId = (int)$this->_registryManager->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->addCustomerIdFilter($customerId);
        $this->setOrder('id', 'DESC');
        parent::_initSelect();

        return $this;
    }
}
