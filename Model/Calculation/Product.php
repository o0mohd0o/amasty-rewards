<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Calculation;

use Amasty\Rewards\Api\Data\RuleInterface;
use Amasty\Rewards\Model\Quote\EarningChecker;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\StoreManagerInterface;

class Product
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Tax
     */
    private $taxCalculation;

    /**
     * @var EarningChecker
     */
    private $earningChecker;

    public function __construct(
        StoreManagerInterface $storeManager,
        Tax $taxCalculation,
        EarningChecker $earningChecker
    ) {
        $this->storeManager = $storeManager;
        $this->taxCalculation = $taxCalculation;
        $this->earningChecker = $earningChecker;
    }

    /**
     * @param array $products
     * @param int $customerId
     * @param RuleInterface $rule
     * @return float
     * @throws NoSuchEntityException
     */
    public function calculatePointsByProduct(array $products, int $customerId, RuleInterface $rule): float
    {
        $totalAmount = 0;
        $productAmount = count($products);

        $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency();

        $this->storeManager->getStore()->setCurrentCurrency($this->storeManager->getStore()->getBaseCurrency());

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($products as $product) {
            if (!$this->earningChecker->isPossibleEarningOnProduct($rule, $product, $productAmount)) {
                continue;
            }

            $amount = $product->getTypeId() === Grouped::TYPE_CODE
                ? $product->getFinalPrice()
                : $product->getFinalPrice() * max($product->getQty(), $product->getCartQty());

            $amount = $this->taxCalculation->correctAmountByTax($product, $amount, $customerId);
            $totalAmount += round($amount, 2);
        }

        $this->storeManager->getStore()->setCurrentCurrency($currentCurrency);

        return floor($totalAmount / $rule->getSpentAmount()) * $rule->getAmount();
    }
}
