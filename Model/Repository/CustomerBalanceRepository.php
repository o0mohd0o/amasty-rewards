<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Repository;

use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Amasty\Rewards\Api\Data\CustomerBalanceInterface;
use Amasty\Rewards\Model\CustomerBalanceFactory;
use Amasty\Rewards\Model\ResourceModel\CustomerBalance as ResourceCustomerBalance;
use Magento\Framework\Exception\CouldNotSaveException;

class CustomerBalanceRepository implements CustomerBalanceRepositoryInterface
{
    /**
     * @var ResourceCustomerBalance
     */
    private $resourceCustomerBalance;

    /**
     * @var CustomerBalanceFactory
     */
    private $customerBalanceFactory;

    /**
     * @var array
     */
    private $balanceEntities;

    public function __construct(
        ResourceCustomerBalance $resourceCustomerBalance,
        CustomerBalanceFactory $customerBalanceFactory
    ) {
        $this->resourceCustomerBalance = $resourceCustomerBalance;
        $this->customerBalanceFactory = $customerBalanceFactory;
    }

    public function getBalanceEntityByCustomerId(int $customerId): CustomerBalanceInterface
    {
        if (!isset($this->balanceEntities[$customerId])) {
            $entity = $this->customerBalanceFactory->create();
            $this->resourceCustomerBalance->load($entity, $customerId, CustomerBalanceInterface::CUSTOMER_ID);
            $this->balanceEntities[$customerId] = $entity;
        }

        return $this->balanceEntities[$customerId];
    }

    /**
     * @param int $customerId
     * @return float
     */
    public function getBalanceByCustomerId(int $customerId): float
    {
        $entity = $this->getBalanceEntityByCustomerId($customerId);

        return $entity->getBalance();
    }

    public function save(CustomerBalanceInterface $entity): void
    {
        try {
            $this->resourceCustomerBalance->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save customer balance: %1',
                    $exception->getMessage()
                )
            );
        }
    }
}
