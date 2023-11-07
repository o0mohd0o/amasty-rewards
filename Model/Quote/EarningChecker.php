<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Quote;

use Amasty\Rewards\Api\Data\RuleInterface;
use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\Points\Converter\ToMoney;
use Amasty\Rewards\Model\SuitableItemChecker;
use Magento\Bundle\Model\Product\Price as BundlePrice;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Price;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Sales\Model\Order\Item as OrderItem;

class EarningChecker
{
    /**
     * @var SuitableItemChecker
     */
    private $suitableItemChecker;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var ToMoney
     */
    private $toMoney;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    public function __construct(
        SuitableItemChecker $suitableItemChecker,
        ItemFactory $itemFactory,
        ToMoney $toMoney,
        ProductRepositoryInterface $productRepository,
        CustomerRegistry $customerRegistry
    ) {
        $this->suitableItemChecker = $suitableItemChecker;
        $this->itemFactory = $itemFactory;
        $this->toMoney = $toMoney;
        $this->productRepository = $productRepository;
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * @param RuleInterface $rule
     * @param Product $product
     * @param int $productAmount
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isPossibleEarningOnProduct(RuleInterface $rule, Product $product, int $productAmount): bool
    {
        /** @var QuoteItem $itemModel */
        $itemModel = $this->itemFactory->create();
        $itemModel->setOptions($product->getCustomOptions());
        $itemModel->setProduct($product);
        $itemModel->setSku($product->getSku());

        if (($this->isNotSimpleAndNotSingleProduct($productAmount, $product)
                && !$this->isBundleProductWithFixedPrice($product))
            || $this->parentIsBundleWithFixedPrice($product)) {
            return false;
        }

        return $this->isPossibleEarningOnItem($rule, $itemModel);
    }

    /**
     * @param RuleInterface $rule
     * @param QuoteItem|OrderItem $item
     * @return bool
     */
    public function isPossibleEarningOnItem(RuleInterface $rule, $item): bool
    {
        if ($this->isBundleProductWithDynamicPrice($item->getProduct())
            || $this->isSkipByDiscount($rule, $item)) {
            return false;
        }

        if ($rule->getGrantPointsForSpecificProducts()
            && $rule->getAction() === 'moneyspent'
            && (!empty($rule->getProductsSku())
                || !empty($rule->getCategories()))
        ) {
            $skuIds = $rule->getProductsSkusArray();
            $categoryIds = $rule->getCategoriesArray();
            $action = $rule->getActionForEarning();

            return $this->suitableItemChecker->isSuitableItem($item, $action, $skuIds, $categoryIds);
        }

        return true;
    }

    /**
     * @param RuleInterface $rule
     * @param QuoteItem|OrderItem $item
     * @return bool
     */
    private function isSkipByDiscount(RuleInterface $rule, $item): bool
    {
        if ($rule->getSkipDiscountedProducts()) {
            $product = $item->getProduct();
            $finalPrice = $product->getPriceInfo()->getPrice(Price::KEY_FINAL_PRICE)->getAmount()->getValue();
            $regularPrice = $product->getPriceInfo()->getPrice(Price::KEY_REGULAR_PRICE)->getAmount()->getValue();

            $discountAmount = 0;

            if ($finalPrice !== $regularPrice) {
                $discountAmount = $regularPrice - $finalPrice;
            }

            if (!$discountAmount && $item->getAppliedRuleIds()) {
                $discountAmount = $item->getDiscountAmount() - $this->getPointsDiscountOnItem($item, $finalPrice);
            }

            if ($discountAmount > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $item
     * @param float $finalPrice
     * @return float
     */
    private function getPointsDiscountOnItem($item, float $finalPrice): float
    {
        $pointsSpent = (float)$item->getData(EntityInterface::POINTS_SPENT);
        $pointsDiscount = 0.0;

        if ($pointsSpent) {
            $pointsDiscount = $this->toMoney->convert($pointsSpent, (int)$item->getStoreId(), $finalPrice);
        }

        return $pointsDiscount;
    }

    /**
     * @param int $customerId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isForbiddenEarningByCustomerStatus(int $customerId): bool
    {
        if (!$customerId) {
            return true;
        }

        $customer = $this->customerRegistry->retrieve($customerId);

        return (bool)$customer->getAmrewardsForbidEarning();
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isBundleProductWithFixedPrice(Product $product): bool
    {
        if ($product->getTypeId() == Type::TYPE_BUNDLE && $product->getPriceType() == BundlePrice::PRICE_TYPE_FIXED) {
            return true;
        }

        return false;
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isBundleProductWithDynamicPrice(Product $product): bool
    {
        if ($product->getTypeId() == Type::TYPE_BUNDLE && $product->getPriceType() == BundlePrice::PRICE_TYPE_DYNAMIC) {
            return true;
        }

        return false;
    }

    /**
     * @param int $productAmount
     * @param Product $product
     * @return bool
     */
    private function isNotSimpleAndNotSingleProduct(int $productAmount, Product $product): bool
    {
        if ($productAmount > 1 && $product->getTypeId() != Type::TYPE_SIMPLE) {
            return true;
        }

        return false;
    }

    /**
     * @param Product $product
     * @return bool
     * @throws NoSuchEntityException
     */
    private function parentIsBundleWithFixedPrice(Product $product): bool
    {
        if ($product->getParentProductId()) {
            $parentProduct = $this->productRepository->getById($product->getParentProductId());
            if ($this->isBundleProductWithFixedPrice($parentProduct)) {
                return true;
            }
        }

        return false;
    }
}
