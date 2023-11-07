<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api\Data;

interface HistoryInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const CUSTOMER_ID = 'customer_id';
    public const DATE = 'date';
    public const ACTION_ID = 'action_id';
    public const PARAMS = 'params';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Rewards\Api\Data\HistoryInterface
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return \Amasty\Rewards\Api\Data\HistoryInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getDate();

    /**
     * @param string $date
     *
     * @return \Amasty\Rewards\Api\Data\HistoryInterface
     */
    public function setDate($date);

    /**
     * @return int
     */
    public function getActionId();

    /**
     * @param int $actionId
     *
     * @return \Amasty\Rewards\Api\Data\HistoryInterface
     */
    public function setActionId($actionId);

    /**
     * @return string
     */
    public function getParams();

    /**
     * @param string $params
     *
     * @return \Amasty\Rewards\Api\Data\HistoryInterface
     */
    public function setParams($params);
}
