<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api;

/**
 * @api
 */
interface HistoryRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Rewards\Api\Data\HistoryInterface $history
     *
     * @return \Amasty\Rewards\Api\Data\HistoryInterface
     */
    public function save(\Amasty\Rewards\Api\Data\HistoryInterface $history);

    /**
     * Get by id
     *
     * @param int $id
     *
     * @return \Amasty\Rewards\Api\Data\HistoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\Rewards\Api\Data\HistoryInterface $history
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Rewards\Api\Data\HistoryInterface $history);

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $customerId
     * @param int $action
     * @param string|null $params
     *
     * @return \Amasty\Rewards\Model\History
     */
    public function getLastActionByCustomerId($customerId, $action, $params = null);

    /**
     * @param int $customerId
     * @param string|null $params
     *
     * @return array|int[]
     */
    public function getAppliedActionsId($customerId, $params = null);

    /**
     * @param int $customerId
     * @param string $startDate
     * @param string|null $params
     *
     * @return mixed
     */
    public function getLastYearActionsId($customerId, $startDate, $params = null);
}
