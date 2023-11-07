<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Calculation\Earning;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Magento\Sales\Model\Order\Item;

class Applier
{
    /**
     * @param Item $item
     * @param float $earnPoints
     */
    public function apply(Item $item, float $earnPoints): void
    {
        $item->setData(EntityInterface::POINTS_EARN, $item->getData(EntityInterface::POINTS_EARN) + $earnPoints);
    }
}
