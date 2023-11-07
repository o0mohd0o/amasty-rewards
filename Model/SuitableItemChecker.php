<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Model\Config\Source\IncludeExclude;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;

class SuitableItemChecker
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param QuoteItem|OrderItem $item
     * @param int $action
     * @param array $skuIds
     * @param array $categoryIds
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSuitableItem($item, int $action, array $skuIds, array $categoryIds): bool
    {
        $itemCategoryIds = $item->getProduct()->getCategoryIds();

        if (!empty($skuIds) && array_intersect($this->getApplicableSkus($item), $skuIds)) {
            return $action === IncludeExclude::INCLUDE_VALUE;
        }

        if (!empty($itemCategoryIds) && array_intersect($itemCategoryIds, $categoryIds)) {
            return $action === IncludeExclude::INCLUDE_VALUE;
        }

        return $action === IncludeExclude::EXCLUDE_VALUE;
    }

    /**
     * @param QuoteItem|OrderItem $quoteItem
     * @return array
     * @throws NoSuchEntityException
     */
    private function getApplicableSkus($quoteItem): array
    {
        $skus = [$quoteItem->getProduct()->getData('sku')];

        if ($quoteItem->getProductType() === Configurable::TYPE_CODE) {
            if ($quoteItem instanceof QuoteItem) {
                $option = $quoteItem->getOptionByCode('simple_product');
                if ($option) {
                    $product = $option->getProduct();
                    $skus[] = $product->getData('sku');
                }
            } else {
                $option = $quoteItem->getData('product_options');
                if ($option && is_array($option) && isset($option['simple_sku'])) {
                    $skus[] = $option['simple_sku'];
                }
            }
        }

        //for validation on the cart, checkout and admin area
        if ($quoteItem->getParentItem()) {
            $skus[] = $quoteItem->getParentItem()->getProduct()->getData('sku');
        }

        //for validation on product page
        if ($quoteItem->getProduct()->getParentProductId()) {
            $skus[] = $this->productRepository->getById($quoteItem->getProduct()->getParentProductId())->getSku();
        }

        return $skus;
    }
}
