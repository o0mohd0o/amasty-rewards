<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel\History;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\Rewards\Model\History::class, \Amasty\Rewards\Model\ResourceModel\History::class);
    }

    public function addCustomerFilter($customerId)
    {
        $this->addFieldToFilter('customer_id', (int)$customerId);
    }

    /**
     * @param string $params
     *
     * @return $this
     */
    public function addParamsFilter($params)
    {
        if (is_string($params)) {
            $this->addFieldToFilter('params', ['like' => '%' . $params . '%']);
        }

        return $this;
    }

    /**
     * @param int $actionId
     * @param int $customerId
     *
     * @return \Amasty\Rewards\Model\History
     */
    public function getLastRewardByRuleIdAndCustomerId($actionId, $customerId)
    {
        $this->addCustomerFilter($customerId);
        $this->addFieldToFilter('action_id', $actionId)
            ->addOrder('date');

        return $this->getFirstItem();
    }
}
