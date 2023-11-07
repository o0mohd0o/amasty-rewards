<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup\Patch\Data;

use Amasty\Rewards\Model\CustomerBalanceFactory;
use Amasty\Rewards\Model\Repository\CustomerBalanceRepository;
use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amasty\Rewards\Model\ResourceModel\Rewards\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateCustomerBalance implements DataPatchInterface
{
    /**
     * @var CustomerBalanceFactory
     */
    private $customerBalanceFactory;

    /**
     * @var CustomerBalanceRepository
     */
    private $customerBalanceRepository;

    /**
     * @var RewardsRepository
     */
    private $rewardsRepository;

    /**
     * @var CollectionFactory
     */
    private $rewardsCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        CustomerBalanceRepository $customerBalanceRepository,
        CustomerBalanceFactory $customerBalanceFactory,
        RewardsRepository $rewardsRepository,
        CollectionFactory $rewardsCollectionFactory,
        ResourceConnection $resourceConnection = null // TODO move to not optional
    ) {
        $this->customerBalanceRepository = $customerBalanceRepository;
        $this->customerBalanceFactory = $customerBalanceFactory;
        $this->rewardsRepository = $rewardsRepository;
        $this->rewardsCollectionFactory = $rewardsCollectionFactory;
        $this->resourceConnection = $resourceConnection ?? ObjectManager::getInstance()->get(ResourceConnection::class);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $customerIds = $this->getCustomerIds();

        foreach ($customerIds as $customerId) {
            $customerBalanceEntity = $this->customerBalanceFactory->create();
            $customerBalanceEntity->setCustomerId((int)$customerId);
            $customerBalanceEntity->setBalance(
                (float)$this->rewardsRepository->getCustomerRewardBalance((int)$customerId)
            );

            $this->customerBalanceRepository->save($customerBalanceEntity);
        }

        return $this;
    }

    /**
     * @return string[]
     */
    private function getCustomerIds(): array
    {
        $rewardsCollection = $this->rewardsCollectionFactory->create();
        $select = $rewardsCollection->getSelect()
            ->join(
                ['customer_entity' => $this->resourceConnection->getTableName('customer_entity')],
                'customer_entity.entity_id = main_table.customer_id',
                []
            )
            ->reset(Select::COLUMNS)
            ->columns('main_table.customer_id')
            ->group('main_table.customer_id');

        return $rewardsCollection->getConnection()->fetchCol($select);
    }
}
