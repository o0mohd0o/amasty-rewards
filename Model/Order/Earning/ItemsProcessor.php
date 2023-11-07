<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Order\Earning;

use Amasty\Rewards\Model\Calculation\Distributor;
use Amasty\Rewards\Model\Calculation\Earning\Applier;
use Amasty\Rewards\Model\Calculation\ItemAmountCalculatorInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Model\Order\Item;

class ItemsProcessor
{
    /**
     * @var Distributor
     */
    private $distributor;

    /**
     * @var Applier
     */
    private $applier;

    /**
     * @var ItemAmountCalculatorInterface
     */
    private $itemAmountCalculator;

    public function __construct(
        Distributor $distributor,
        Applier $applier,
        ItemAmountCalculatorInterface $itemAmountCalculator
    ) {
        $this->distributor = $distributor;
        $this->applier = $applier;
        $this->itemAmountCalculator = $itemAmountCalculator;
    }

    /**
     * @param Item[] $items
     * @param float $earnedPoints
     */
    public function process(array $items, float $earnedPoints): void
    {
        $itemsAmount = $this->getAllItemsAmount($items);
        $percent = ($earnedPoints * 100) / $itemsAmount;
        $itemsPoints = $this->distributor->distribute($items, $earnedPoints, $percent);

        foreach ($items as $item) {
            if ($item->getProductType() === Type::TYPE_BUNDLE) {
                continue;
            }

            $itemPoints = $itemsPoints[$item->getId()] ?? 0;
            $this->applier->apply($item, (float)$itemPoints);
            $earnedPoints += $itemsPoints[$item->getId()];
        }
    }

    /**
     * @param Item[] $items
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
