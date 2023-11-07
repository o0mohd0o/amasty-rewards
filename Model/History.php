<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */
namespace Amasty\Rewards\Model;

use Magento\Framework\Model\AbstractModel;
use Amasty\Rewards\Api\Data\HistoryInterface;

class History extends AbstractModel implements HistoryInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\History::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(HistoryInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(HistoryInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDate()
    {
        return $this->_getData(HistoryInterface::DATE);
    }

    /**
     * @inheritdoc
     */
    public function setDate($date)
    {
        $this->setData(HistoryInterface::DATE, $date);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getActionId()
    {
        return $this->_getData(HistoryInterface::ACTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setActionId($actionId)
    {
        $this->setData(HistoryInterface::ACTION_ID, $actionId);

        return $this;
    }

    /**
     * @return string
     */
    public function getParams()
    {
        return $this->_getData(HistoryInterface::PARAMS);
    }

    /**
     * @param string $params
     *
     * @return \Amasty\Rewards\Api\Data\HistoryInterface
     */
    public function setParams($params)
    {
        $this->setData(HistoryInterface::PARAMS, $params);

        return $this;
    }
}
