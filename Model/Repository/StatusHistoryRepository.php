<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Repository;

use Amasty\Rewards\Api\Data\StatusHistoryInterface;
use Amasty\Rewards\Api\StatusHistoryRepositoryInterface;
use Amasty\Rewards\Model\ResourceModel\StatusHistory as ResourceStatusHistory;
use Amasty\Rewards\Model\ResourceModel\StatusHistory\CollectionFactory;
use Amasty\Rewards\Model\StatusHistoryFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class StatusHistoryRepository implements StatusHistoryRepositoryInterface
{
    /**
     * @var ResourceStatusHistory
     */
    private $resourceStatusHistory;

    /**
     * @var StatusHistoryFactory
     */
    private $statusHistoryFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        ResourceStatusHistory $resourceStatusHistory,
        StatusHistoryFactory $statusHistoryFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resourceStatusHistory = $resourceStatusHistory;
        $this->statusHistoryFactory = $statusHistoryFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param StatusHistoryInterface $statusEntity
     * @return StatusHistoryInterface
     * @throws CouldNotSaveException
     */
    public function save(StatusHistoryInterface $statusEntity): StatusHistoryInterface
    {
        try {
            $this->resourceStatusHistory->save($statusEntity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save status history: %1',
                    $exception->getMessage()
                )
            );
        }

        return $statusEntity;
    }

    /**
     * @param int $customerId
     * @param string $creationDate
     * @return StatusHistoryInterface|null
     * @throws LocalizedException
     */
    public function getByCustomerIdAndDate(int $customerId, string $creationDate): ?StatusHistoryInterface
    {
        $statusEntity = $this->statusHistoryFactory->create();
        $this->resourceStatusHistory->loadByCustomerIdAndDate($statusEntity, $customerId, $creationDate);

        return $statusEntity;
    }

    /**
     * @param int $entityId
     * @return StatusHistoryInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): StatusHistoryInterface
    {
        $statusEntity = $this->statusHistoryFactory->create();
        $this->resourceStatusHistory->load($statusEntity, $entityId);

        if (!$statusEntity->getId()) {
            throw new NoSuchEntityException(__('Status history entity with specified ID "%1" not found.', $entityId));
        }

        return $statusEntity;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
