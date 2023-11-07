<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Catalog\Highlight;

use Amasty\Base\Model\Serializer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\DataObjectFactory;

/**
 * Factory class creating dummy object for Highlight validation
 */
class ValidObjectFromDataFactory
{
    /**
     * @var ValidObjectFactory
     */
    private $objectFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        ValidObjectFactory $objectFactory,
        CustomerFactory $customerFactory,
        DataObjectFactory $dataObjectFactory,
        CustomerResource $customerResource,
        ProductRepositoryInterface $productRepository,
        Serializer $serializer
    ) {
        $this->objectFactory = $objectFactory;
        $this->customerFactory = $customerFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerResource = $customerResource;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
    }

    /**
     * @param int $productId
     * @param string $params is serialized data from product form
     * @param int $customerId
     * @param null|string $store
     *
     * @return ValidObject
     */
    public function create($productId, $params, $customerId, $store = null)
    {
        /** @var \Amasty\Rewards\Model\Catalog\Highlight\ValidObject $object */
        $object = $this->objectFactory->create();

        //Customer initialization
        if ($this->customer) {
            $object->setCustomer($this->customer);
        }

        if (!$object->hasValidCustomer($customerId)) {
            $object->setCustomer($this->customerFactory->create());
            $this->customerResource->load($object->getCustomer(), $customerId);
            $this->customer = $object->getCustomer();
        } //end initialization

        //Product initialization
        //Do not store product because it always should be another one

        $attributesArray = [];
        // phpcsignore because there is no better option
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        parse_str((string)$params, $attributesArray);

        $product = $this->productRepository->getById((int)$productId, false, $store);

        if (isset($attributesArray['amconfigurable-option'])) {
            //compatibility with Color Swatches Pro
            foreach ($attributesArray['amconfigurable-option'] as $optionValues) {
                $options = $this->serializer->unserialize($optionValues);
                $qty = (float)$options['qty'];
                if ($qty < 0.00001) {
                    continue;
                }
                unset($options['qty']);
                $tmpRequest = ['super_attribute' => []];

                foreach ($options as $attribute => $value) {
                    $tmpRequest['super_attribute'][(int)$attribute] = (int)$value;
                }
                $tmpRequest['qty'] = $qty;

                $product = clone $product;

                $object->setProduct(
                    $product,
                    $this->getRequest($tmpRequest)
                );
            }
        } else {
            $object->setProduct(
                $product,
                $this->getRequest($attributesArray)
            );
        }//end initialization

        return $object;
    }

    /**
     * Convert array to object
     *
     * @param array $attributesArray
     *
     * @return \Magento\Framework\DataObject
     */
    private function getRequest($attributesArray)
    {
        //Fix for category page because there are no qty field
        if (!isset($attributesArray['qty'])) {
            $attributesArray['qty'] = 1;
        }

        return $this->dataObjectFactory->create()->addData($attributesArray);
    }
}
