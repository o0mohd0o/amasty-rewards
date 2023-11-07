<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Repository;

use Amasty\Rewards\Api\Data\HistoryInterface;
use Amasty\Rewards\Api\HistoryRepositoryInterface;
use Amasty\Rewards\Model\HistoryFactory;
use Amasty\Rewards\Model\ResourceModel\History as HistoryResource;
use Amasty\Rewards\Model\ResourceModel\History\Collection;
use Amasty\Rewards\Model\ResourceModel\History\CollectionFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HistoryRepository implements HistoryRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var HistoryResource
     */
    private $historyResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $historys;

    /**
     * @var CollectionFactory
     */
    private $historyCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        HistoryFactory $historyFactory,
        HistoryResource $historyResource,
        CollectionFactory $historyCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->historyCollectionFactory = $historyCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(HistoryInterface $history)
    {
        try {
            if ($history->getId()) {
                $history = $this->getById($history->getId())->addData($history->getData());
            }
            $this->historyResource->save($history);
            unset($this->historys[$history->getId()]);
        } catch (\Exception $e) {
            if ($history->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save history with ID %1. Error: %2',
                        [$history->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new history. Error: %1', $e->getMessage()));
        }

        return $history;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->historys[$id])) {
            /** @var \Amasty\Rewards\Model\History $history */
            $history = $this->historyFactory->create();
            $this->historyResource->load($history, $id);
            if (!$history->getId()) {
                throw new NoSuchEntityException(__('History with specified ID "%1" not found.', $id));
            }
            $this->historys[$id] = $history;
        }

        return $this->historys[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(HistoryInterface $history)
    {
        try {
            $this->historyResource->delete($history);
            unset($this->historys[$history->getId()]);
        } catch (\Exception $e) {
            if ($history->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove history with ID %1. Error: %2',
                        [$history->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove history. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $historyModel = $this->getById($id);
        $this->delete($historyModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Rewards\Model\ResourceModel\History\Collection $historyCollection */
        $historyCollection = $this->historyCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $historyCollection);
        }

        $searchResults->setTotalCount($historyCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $historyCollection);
        }

        $historyCollection->setCurPage($searchCriteria->getCurrentPage());
        $historyCollection->setPageSize($searchCriteria->getPageSize());

        $historys = [];
        /** @var HistoryInterface $history */
        foreach ($historyCollection->getItems() as $history) {
            $historys[] = $this->getById($history->getId());
        }

        $searchResults->setItems($historys);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $historyCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $historyCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $historyCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $historyCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $historyCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $historyCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @return \Amasty\Rewards\Api\Data\HistoryInterface
     */
    public function getEmptyModel()
    {
        return $this->historyFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getLastActionByCustomerId($customerId, $action, $params = null)
    {
        $collection = $this->historyCollectionFactory->create()->addParamsFilter($params);

        return $collection->getLastRewardByRuleIdAndCustomerId($action, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function getAppliedActionsId($customerId, $params = null)
    {
        $actions = $this->historyResource->getAppliedActions($customerId, $params);
        $appliedActions = [];

        foreach ($actions as $action) {
            $appliedActions[$action[HistoryInterface::ACTION_ID]] = +1;
        }

        return $appliedActions;
    }

    /**
     * @inheritdoc
     */
    public function getLastYearActionsId($customerId, $startDate, $params = null)
    {
        $actions = $this->historyResource->getLastYearActions($customerId, $startDate, $params);
        $appliedActions = [];

        foreach ($actions as $action) {
            if (isset($appliedActions[$action[HistoryInterface::ACTION_ID]])) {
                $appliedActions[$action[HistoryInterface::ACTION_ID]]++;

                continue;
            }
            $appliedActions[$action[HistoryInterface::ACTION_ID]] = 1;
        }

        return $appliedActions;
    }
}
