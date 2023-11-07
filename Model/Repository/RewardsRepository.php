<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Repository;

use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Amasty\Rewards\Model\ResourceModel\Rewards as RewardsResource;
use Amasty\Rewards\Model\ResourceModel\Rewards\Collection;
use Amasty\Rewards\Model\ResourceModel\Rewards\CollectionFactory;
use Amasty\Rewards\Model\RewardsFactory;
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
class RewardsRepository implements RewardsRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var RewardsFactory
     */
    private $rewardsFactory;

    /**
     * @var RewardsResource
     */
    private $rewardsResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $rewardModels;

    /**
     * @var CollectionFactory
     */
    private $rewardsCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        RewardsFactory $rewardsFactory,
        RewardsResource $rewardsResource,
        CollectionFactory $rewardsCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->rewardsFactory = $rewardsFactory;
        $this->rewardsResource = $rewardsResource;
        $this->rewardsCollectionFactory = $rewardsCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(RewardsInterface $rewards)
    {
        try {
            if ($rewards->getId()) {
                $rewards = $this->getById($rewards->getId())->addData($rewards->getData());
            }
            $this->rewardsResource->save($rewards);
            unset($this->rewardModels[$rewards->getId()]);
        } catch (\Exception $e) {
            if ($rewards->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save rewards with ID %1. Error: %2',
                        [$rewards->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new rewards. Error: %1', $e->getMessage()));
        }

        return $rewards;
    }

    /**
     * @param int $id
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        if (!isset($this->rewardModels[$id])) {
            /** @var \Amasty\Rewards\Model\Rewards $rewards */
            $rewards = $this->rewardsFactory->create();
            $this->rewardsResource->load($rewards, $id);
            if (!$rewards->getId()) {
                throw new NoSuchEntityException(__('Rewards with specified ID "%1" not found.', $id));
            }
            $this->rewardModels[$id] = $rewards;
        }

        return $this->rewardModels[$id];
    }

    /**
     * @param int $customerId
     * @param int $limit
     * @param int $page
     * @return \Amasty\Rewards\Api\Data\RewardsInterface[]
     */
    public function getByCustomerId($customerId, $limit = 10, $page = 1): array
    {
        /** @var Collection $collection */
        $collection = $this->rewardsCollectionFactory->create();
        $collection->addCustomerIdFilter($customerId)->setPageSize($limit);

        return $collection->getItems();
    }

    /**
     * @param RewardsInterface $rewards
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RewardsInterface $rewards)
    {
        try {
            $this->rewardsResource->delete($rewards);
            unset($this->rewardModels[$rewards->getId()]);
        } catch (\Exception $e) {
            if ($rewards->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove rewards with ID %1. Error: %2',
                        [$rewards->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove rewards. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        $rewardsModel = $this->getById($id);
        $this->delete($rewardsModel);

        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $rewardsCollection */
        $rewardsCollection = $this->rewardsCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $rewardsCollection);
        }

        $searchResults->setTotalCount($rewardsCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $rewardsCollection);
        }

        $rewardsCollection->setCurPage($searchCriteria->getCurrentPage());
        $rewardsCollection->setPageSize($searchCriteria->getPageSize());

        $rewardModels = [];
        /** @var RewardsInterface $rewards */
        foreach ($rewardsCollection->getItems() as $rewards) {
            $rewardModels[] = $this->getById($rewards->getId());
        }

        $searchResults->setItems($rewardModels);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $rewardsCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $rewardsCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $rewardsCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $rewardsCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $rewardsCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $rewardsCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @param int $customerId
     * @return float|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerRewardBalance($customerId)
    {
        $dataArray = current($this->rewardsResource->getRewards($customerId));

        if (is_array($dataArray) && !empty($dataArray[RewardsInterface::AMOUNT])) {
            return $dataArray[RewardsInterface::AMOUNT];
        }

        return 0;
    }

    /**
     * @return RewardsInterface
     */
    public function getEmptyModel()
    {
        return $this->rewardsFactory->create();
    }
}
