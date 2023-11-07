<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel\Rule;

class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DataObject $associatedEntityMap,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_associatedEntitiesMap = $associatedEntityMap->getData();
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Rewards\Model\Rule', 'Amasty\Rewards\Model\ResourceModel\Rule');
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
    }

    public function loadByAction($action)
    {
        $this->getSelect()->where(
            'action = ?',
            $action
        );
    }

    /**
     * Filter collection by website(s), customer group(s) and date.
     * Filter collection to only active rules.
     * Sorting is not involved
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param $action
     * @use $this->addWebsiteFilter()
     * @return $this
     */
    public function addWebsiteGroupActionFilter($websiteId, $customerGroupId, $action)
    {
        if (!$this->getFlag('website_group_date_filter')) {

            $this->addWebsiteFilter($websiteId);

            $entityInfo = $this->_getAssociatedEntityInfo('customer_group');
            $connection = $this->getConnection();
            $this->getSelect()->joinInner(
                [
                    'customer_group_ids' => $this->getTable($entityInfo['associations_table'])
                ],
                $connection->quoteInto(
                    'main_table.' .
                    $entityInfo['rule_id_field'] .
                    ' = customer_group_ids.' .
                    $entityInfo['rule_id_field'] .
                    ' AND customer_group_ids.' .
                    $entityInfo['entity_id_field'] .
                    ' = ?',
                    (int)$customerGroupId
                ),
                []
            );

            $this->getSelect()->where(
                $this->getConnection()->quoteInto('action = ?', $action)
            );

            $this->addIsActiveFilter();

            $this->setFlag('website_group_date_filter', true);
        }

        return $this;
    }
}
