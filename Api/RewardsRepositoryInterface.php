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
interface RewardsRepositoryInterface
{
    /**
     * @param \Amasty\Rewards\Api\Data\RewardsInterface $rewards
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     */
    public function save(\Amasty\Rewards\Api\Data\RewardsInterface $rewards);

    /**
     * @param int $id
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function getById($id);

    /**
     * @param int $customerId
     * @param int $limit
     * @param int $page
     * @return \Amasty\Rewards\Api\Data\RewardsInterface[]
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function getByCustomerId($customerId, $limit = 10, $page = 1): array;

    /**
     * @param \Amasty\Rewards\Api\Data\RewardsInterface $rewards
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function delete(\Amasty\Rewards\Api\Data\RewardsInterface $rewards);

    /**
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function deleteById($id);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $customerId
     * @return float|int
     */
    public function getCustomerRewardBalance($customerId);
}
