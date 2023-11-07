<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api;

use Amasty\Rewards\Api\Data\StatusHistoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

interface StatusHistoryRepositoryInterface
{
    /**
     * @param StatusHistoryInterface $statusEntity
     * @return StatusHistoryInterface
     */
    public function save(StatusHistoryInterface $statusEntity): StatusHistoryInterface;

    /**
     * @param int $customerId
     * @param string $creationDate
     * @return StatusHistoryInterface|null
     */
    public function getByCustomerIdAndDate(int $customerId, string $creationDate): ?StatusHistoryInterface;

    /**
     * @param int $entityId
     * @return StatusHistoryInterface
     */
    public function get(int $entityId): StatusHistoryInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
}
