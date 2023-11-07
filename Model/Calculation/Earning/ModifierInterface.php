<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Calculation\Earning;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;

interface ModifierInterface
{
    /**
     * Modify item amount based for points earning calculation
     *
     * @param QuoteItem|OrderItem $item
     * @param float $calculatedAmount
     * @return float
     */
    public function modifyItemAmount($item, float $calculatedAmount): float;
}
