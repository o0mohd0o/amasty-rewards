<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Ui\DataProvider\Status;

use Amasty\Rewards\Model\ResourceModel\StatusHistory\CollectionFactory;
use Magento\Backend\Model\Session;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\DataProvider\AbstractDataProvider;

class ListingDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        CollectionFactory $collectionFactory,
        Session $session,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->session = $session;
    }

    /**
     * @return AbstractCollection
     */
    public function getCollection(): AbstractCollection
    {
        $customerData = $this->session->getCustomerData();
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create();
            $this->collection->addCustomerFilter((int)$customerData['customer_id']);
        }

        return parent::getCollection();
    }
}
