<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Calculation;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;

class Distributor
{
    /**
     * @var ItemAmountCalculatorInterface
     */
    private $itemAmountCalculator;

    public function __construct(
        ItemAmountCalculatorInterface $itemAmountCalculator
    ) {
        $this->itemAmountCalculator = $itemAmountCalculator;
    }

    /**
     * @param QuoteItem[]|OrderItem[] $items
     * @param float $amountToDistribute
     * @param float $percent
     * @return array where key - item ID, value - item amount value
     */
    public function distribute(array $items, float $amountToDistribute, float $percent): array
    {
        $itemsAmount = [];

        foreach ($items as $item) {
            $itemPrice = $this->itemAmountCalculator->calculateItemAmount($item);
            $amount = ($itemPrice * $percent) / 100;
            $amountToDistribute -= $amount;
            $itemsAmount[$item->getId()] = $amount;
            $lastItemId = $item->getId();
        }

        if (isset($lastItemId)) {
            $itemsAmount[$lastItemId] += $amountToDistribute;
        }

        return $itemsAmount;
    }
}
