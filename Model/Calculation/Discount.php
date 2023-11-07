<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Calculation;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\Calculation\Discount\Applier;
use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\Points\Converter\ToMoney;
use Amasty\Rewards\Model\Quote\SpendingChecker;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfig;

class Discount
{
    /**
     * @var Config
     */
    private $rewardsConfig;

    /**
     * @var Applier
     */
    private $discountApplier;

    /**
     * @var Distributor
     */
    private $discountDistributor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SpendingChecker
     */
    private $spendingChecker;

    /**
     * @var ToMoney
     */
    private $toMoney;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    public function __construct(
        Config $rewardsConfig,
        Applier $discountApplier,
        Distributor $discountDistributor,
        StoreManagerInterface $storeManager,
        SpendingChecker $spendingChecker,
        ToMoney $toMoney,
        TaxConfig $taxConfig
    ) {
        $this->rewardsConfig = $rewardsConfig;
        $this->discountApplier = $discountApplier;
        $this->discountDistributor = $discountDistributor;
        $this->storeManager = $storeManager;
        $this->spendingChecker = $spendingChecker;
        $this->toMoney = $toMoney;
        $this->taxConfig = $taxConfig;
    }

    /**
     * @param Item[] $quoteItems
     * @param Total $total
     * @param float $points
     *
     * @return float
     */
    public function calculateDiscount(array $quoteItems, Total $total, float $points): float
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $rate = $this->rewardsConfig->getPointsRate($storeId);
        $items = $this->filterItems($quoteItems, $storeId);
        $allCartPrice = $this->getAllItemsPrice($items);

        if (!$points || !$rate || !$items || !$allCartPrice) {
            return 0;
        }

        usort($items, [$this, 'sortItems']);

        $roundRule = $this->rewardsConfig->getRoundRule($storeId);
        $basePoints = $this->toMoney->convert($points, $storeId, $allCartPrice);
        $percent = ($basePoints * 100) / $allCartPrice;
        $itemsDiscount = $this->discountDistributor->distribute($items, $basePoints, $percent);
        $discountValue = 0;

        foreach ($items as $item) {
            $itemDiscount = $itemsDiscount[$item->getId()] ?? 0;
            $this->discountApplier->apply($item, $total, (float)$itemDiscount, $rate);
            $discountValue += $itemsDiscount[$item->getId()];
        }

        $appliedPoints = $discountValue * $rate;

        if ($roundRule === 'up') {
            $appliedPoints = ceil($appliedPoints);
        }

        return (float)$appliedPoints;
    }

    /**
     * @param Item[] $quoteItems
     */
    public function clearPointsDiscount(array $quoteItems): void
    {
        foreach ($quoteItems as $item) {
            $this->discountApplier->clear($item);
        }
    }

    /**
     * @param Item[] $items
     * @return float
     */
    private function getAllItemsPrice(array $items): float
    {
        $allItemsPrice = 0;

        foreach ($items as $item) {
            $allItemsPrice += $this->getRealItemPrice($item);
        }

        return (float)$allItemsPrice;
    }

    /**
     * @param AbstractItem $item
     * @return float
     */
    private function getRealItemPrice(AbstractItem $item): float
    {
        if (!$this->taxConfig->discountTax()) {
            $itemPrice = $item->getBasePrice();
        } else {
            $itemPrice = $item->getBasePriceInclTax();
        }
        $realPrice = ($itemPrice * $item->getQty()) - $item->getBaseDiscountAmount();

        return (float)max(0, $realPrice);
    }

    /**
     * Sorting items before apply reward points
     * cheapest should go first
     *
     * @param AbstractItem $itemA
     * @param AbstractItem $itemB
     *
     * @return int
     */
    private function sortItems(AbstractItem $itemA, AbstractItem $itemB): int
    {
        if ($this->getRealItemPrice($itemA) > $this->getRealItemPrice($itemB)) {
            return 1;
        }

        if ($this->getRealItemPrice($itemA) < $this->getRealItemPrice($itemB)) {
            return -1;
        }

        return 0;
    }

    /**
     * @param array $items
     * @param int $storeId
     * @return array
     */
    private function filterItems(array $items, int $storeId): array
    {
        $filteredItems = [];

        foreach ($items as $item) {
            if (!$this->spendingChecker->isPossibleSpendOnItem($item, $storeId)) {
                $item->setData(EntityInterface::POINTS_SPENT, 0);
                continue;
            }

            $filteredItems[$item->getId()] = $item;
        }

        return $filteredItems;
    }
}
