<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Calculation;

use Amasty\Rewards\Api\Data\RuleInterface;
use Amasty\Rewards\Model\Calculation\Earning\Applier;
use Amasty\Rewards\Model\Calculation\Earning\ItemAmountCalculator;
use Amasty\Rewards\Model\Quote\EarningChecker;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order;

class Earning
{
    /**
     * @var EarningChecker
     */
    private $earningChecker;

    /**
     * @var ItemAmountCalculator
     */
    private $itemAmountCalculator;

    /**
     * @var Distributor
     */
    private $distributor;

    /**
     * @var Applier
     */
    private $applier;

    public function __construct(
        EarningChecker $earningChecker,
        ItemAmountCalculator $itemAmountCalculator,
        Distributor $distributor,
        Applier $applier
    ) {
        $this->earningChecker = $earningChecker;
        $this->itemAmountCalculator = $itemAmountCalculator;
        $this->distributor = $distributor;
        $this->applier = $applier;
    }

    /**
     * @param Address $address
     * @param RuleInterface $rule
     * @return float
     */
    public function calculateByAddress(Address $address, RuleInterface $rule): float
    {
        $items = $this->filterItems($rule, $address->getQuote()->getAllItems());

        return $this->calculate($rule, $items);
    }

    /**
     * @param Order $order
     * @param RuleInterface $rule
     * @return float
     */
    public function calculateByOrder(Order $order, RuleInterface $rule): float
    {
        $items = $this->filterItems($rule, $order->getAllItems());
        $itemsAmount = $this->getAllItemsAmount($items);

        if (empty($items) || !$itemsAmount) {
            return 0;
        }

        $pointsToEarn = $this->calculate($rule, $items);
        $percent = ($pointsToEarn * 100) / $itemsAmount;
        $itemsPoints = $this->distributor->distribute($items, $pointsToEarn, $percent);
        $earnedPoints = 0;

        foreach ($items as $item) {
            $itemPoints = $itemsPoints[$item->getId()] ?? 0;
            $this->applier->apply($item, (float)$itemPoints);
            $earnedPoints += $itemsPoints[$item->getId()];
        }

        return $earnedPoints;
    }

    /**
     * @param RuleInterface $rule
     * @param QuoteItem[]|OrderItem[] $items
     * @return float
     */
    private function calculate(RuleInterface $rule, array $items): float
    {
        $spentAmount = $rule->getSpentAmount();
        $rewardAmount = $rule->getAmount();
        $itemsAmount = $this->getAllItemsAmount($items);

        return floor($itemsAmount / $spentAmount) * $rewardAmount;
    }

    /**
     * @param RuleInterface $rule
     * @param QuoteItem[]|OrderItem[] $items
     * @return array
     */
    private function filterItems(RuleInterface $rule, array $items): array
    {
        $filtered = [];

        foreach ($items as $item) {
            if (!$this->earningChecker->isPossibleEarningOnItem($rule, $item)) {
                continue;
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    /**
     * @param QuoteItem[]|OrderItem[] $items
     * @return float
     */
    private function getAllItemsAmount(array $items): float
    {
        $itemsAmount = 0;

        foreach ($items as $item) {
            $itemsAmount += $this->itemAmountCalculator->calculateItemAmount($item);
        }

        return (float)$itemsAmount;
    }
}
