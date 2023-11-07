<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Rule;

use Amasty\Rewards\Model\ConstantRegistryInterface;
use Amasty\Rewards\Model\ResourceModel\Rule\Collection;
use Amasty\Rewards\Model\ResourceModel\Rule as ResourceModel;
use Amasty\Rewards\Model\ResourceModel\Rule\CollectionFactory;
use Amasty\Rewards\Model\Rule;
use Amasty\Rewards\Model\Rule\Metadata\ValueProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * DataProvider for rewards
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var ResourceModel
     */
    private $resource;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ValueProvider
     */
    private $metadataValueProvider;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PoolInterface
     */
    private $modifiersPool;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        ResourceModel $resource,
        Registry $registry,
        ValueProvider $metadataValueProvider,
        DataPersistorInterface $dataPersistor,
        PoolInterface $modifiersPool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->resource = $resource;
        $this->registry = $registry;
        $this->metadataValueProvider = $metadataValueProvider;
        $this->dataPersistor = $dataPersistor;
        $meta = array_replace_recursive($this->getMetadataValues(), $meta);
        $this->modifiersPool = $modifiersPool;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get metadata values
     *
     * @return array
     */
    protected function getMetadataValues()
    {
        $rule = $this->registry->registry(ConstantRegistryInterface::CURRENT_REWARD);

        return $this->metadataValueProvider->getMetadataValues($rule);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        /** @var Rule $rule */
        foreach ($items as $rule) {
            $this->resource->load($rule, $rule->getId());
            $this->loadedData[$rule->getId()] = $rule->getData();
        }

        $data = $this->dataPersistor->get(ConstantRegistryInterface::FORM_NAMESPACE);
        if (!empty($data)) {
            /** @var Rule $rule */
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->loadedData[$rule->getId()] = $rule->getData();
            $this->dataPersistor->clear(ConstantRegistryInterface::FORM_NAMESPACE);
        }

        /** @var ModifierInterface $modifier */
        foreach ($this->modifiersPool->getModifiersInstances() as $modifier) {
            if ($this->loadedData) {
                $this->loadedData = $modifier->modifyData($this->loadedData);
            }
        }
        if (!$this->loadedData) {
            $this->loadedData = $this->data;
        }

        return $this->loadedData;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->modifiersPool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
