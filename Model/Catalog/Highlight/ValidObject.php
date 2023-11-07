<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Catalog\Highlight;

use Magento\Catalog\Model\Product\Type\AbstractType;

/**
 * Class HighlightValidObject used for emulating behavior of Quote\Address to pass validation
 */
class ValidObject extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var null|\Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product
     */
    private $product = null;

    /**
     * @var null|\Magento\Catalog\Model\Product[]
     */
    private $productCandidates = [];

    /**
     * @var null|\Magento\Customer\Model\Customer
     */
    private $customer = null;

    /**
     * Hack function for validation product as quote item
     *
     * @return array
     */
    public function getAllItems()
    {
        $result = [];

        foreach ($this->getProductCandidates() as $candidate) {
            $product = clone $this;
            $result[] = $product->setProduct($candidate);
        }

        return $result;
    }

    /**
     * @param int $customerId
     *
     * @return bool
     */
    public function hasValidCustomer($customerId)
    {
        return $this->customer instanceof \Magento\Customer\Model\Customer
            && $this->customer->getId() == $customerId;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     *
     * @param \Magento\Framework\DataObject $request
     *
     * @return ValidObject
     */
    public function setProduct(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Framework\DataObject $request = null
    ) {
        $this->product = $product;

        if ($request) {
            $candidates = $product->getTypeInstance()
                ->prepareForCartAdvanced($request, $product, AbstractType::PROCESS_MODE_LITE);
            if (is_array($candidates)) {
                $this->productCandidates = array_merge($this->productCandidates, $candidates);
            } else {
                $this->productCandidates = [$this->getProduct()];
            }
        }

        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product[]|null
     */
    public function getProductCandidates()
    {
        return $this->productCandidates;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return \Magento\Customer\Model\Customer|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param \Magento\Customer\Model\Customer|null $customer
     *
     * @return ValidObject
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }
}
