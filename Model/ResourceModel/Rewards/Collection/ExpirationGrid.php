<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel\Rewards\Collection;

use Magento\Customer\Controller\RegistryConstants;

class ExpirationGrid extends \Amasty\Rewards\Model\ResourceModel\Rewards\Collection
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
        $this->addEmptyExpirationDateFilter();
        parent::_initSelect();

        return $this;
    }

    /**
     * Get SQL for get record count.
     * Overridden because parent method does not add row with null date into COUNT.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);

        $entityColumn = $this->getResource()->getIdFieldName();
        $countSelect->columns(new \Zend_Db_Expr(("COUNT( " . $entityColumn . ")")));

        return $countSelect;
    }
}
