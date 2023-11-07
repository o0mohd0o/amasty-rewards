<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */
/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Rewards\Model\ResourceModel;

use Magento\Rule\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\AbstractModel;

class Rule extends AbstractResource
{
    public const TABLE_NAME = 'amasty_rewards_rule';
    public const TABLE_NAME_LABEL = 'amasty_rewards_rule_label';

    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [];

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    private $string;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\DataObject $associatedEntityMap,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_associatedEntitiesMap = $associatedEntityMap->getData();
        $this->string = $string;
        $this->entityManager = $entityManager;
    }

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'rule_id');
    }

    /**
     * @return string
     */
    public function getLabelTable()
    {
        return $this->getTable(self::TABLE_NAME_LABEL);
    }

    /**
     * Save rule labels for different store views
     *
     * @param int $ruleId
     * @param array $labels
     * @throws \Exception
     * @return $this
     */
    public function saveStoreLabels($ruleId, $labels)
    {
        $deleteByStoreIds = [];
        $table = $this->getLabelTable();
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $storeId => $label) {
            if ($this->string->strlen($label)) {
                $data[] = ['rule_id' => $ruleId, 'store_id' => $storeId, 'label' => $label];
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }

        $connection->beginTransaction();
        try {
            if (!empty($data)) {
                $connection->insertOnDuplicate($table, $data, ['label']);
            }

            if (!empty($deleteByStoreIds)) {
                $connection->delete($table, ['rule_id=?' => $ruleId, 'store_id IN (?)' => $deleteByStoreIds]);
            }
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
        $connection->commit();

        return $this;
    }

    /**
     * Get all existing rule labels
     *
     * @param int $ruleId
     * @return array
     */
    public function getStoreLabels($ruleId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getLabelTable(),
            ['store_id', 'label']
        )->where(
            'rule_id = :rule_id'
        );
        return $this->getConnection()->fetchPairs($select, [':rule_id' => $ruleId]);
    }

    /**
     * Get rule label by specific store id
     *
     * @param int $ruleId
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($ruleId, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getLabelTable(),
            'label'
        )->where(
            'rule_id = :rule_id'
        )->where(
            'store_id IN(0, :store_id)'
        )->order(
            'store_id DESC'
        );
        return $this->getConnection()->fetchOne($select, [':rule_id' => $ruleId, ':store_id' => $storeId]);
    }

    /**
     * Load an object
     *
     * @param SalesRule|AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $this->entityManager->load($object, $value);
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * Delete the object
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }

    /**
     * Save rule's associated store labels.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasStoreLabels()) {
            $this->saveStoreLabels($object->getId(), $object->getStoreLabels());
        }

        parent::_afterSave($object);
        return $this;
    }
}
