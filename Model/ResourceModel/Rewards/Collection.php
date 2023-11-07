<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel\Rewards;

use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Model\ResourceModel\Rewards as RewardsResource;
use Amasty\Rewards\Model\Rewards;
use Magento\Framework\DB\Select;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registryManager;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_registryManager = $registry;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Initialize resource model for collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Rewards::class, RewardsResource::class);
    }

    /**
     * Add filtration by customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function addCustomerIdFilter($customerId)
    {
        $this->getSelect()->where(
            'main_table.customer_id = ?',
            $customerId
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addEmptyExpirationDateFilter()
    {
        $this->getSelect()->where(
            'main_table.expiration_date IS NOT NULL'
        );

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function addExpiration($date)
    {
        $this->getSelect()->columns(
            'DATEDIFF(expiration_date, \'' . $date . '\') as days_left'
        )->order('main_table.id DESC');

        return $this;
    }

    public function addStatistic()
    {
        $this->getSelect()->columns('SUM(amount)');
    }

    /**
     * @param int $customerId
     */
    public function getPointsByCustomerId(int $customerId): void
    {
        $this->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(
                [
                    RewardsInterface::ID,
                    RewardsInterface::AMOUNT,
                    RewardsInterface::EXPIRING_AMOUNT,
                    RewardsInterface::EXPIRATION_DATE
                ]
            )
            ->order(['ISNULL(' . RewardsInterface::EXPIRATION_DATE . ')', RewardsInterface::EXPIRATION_DATE . ' ASC'])
            ->where(RewardsInterface::CUSTOMER_ID . ' = ?', $customerId);
    }
}
