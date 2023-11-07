<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Calculation\Earning\OrderComplete;

use Amasty\Rewards\Model\Calculation\ItemAmountCalculatorInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;

class ItemAmountCalculator implements ItemAmountCalculatorInterface
{
    /**
     * @param QuoteItem|OrderItem $item
     * @return float
     */
    public function calculateItemAmount($item): float
    {
        return (float)$item->getBaseRowTotal();
    }
}
