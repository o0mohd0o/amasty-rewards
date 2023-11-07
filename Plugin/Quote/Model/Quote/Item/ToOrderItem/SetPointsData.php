<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Quote\Model\Quote\Item\ToOrderItem;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Model\Order\Item as OrderItem;

class SetPointsData
{
    /**
     * @see ToOrderItem::convert()
     *
     * @param ToOrderItem $subject
     * @param OrderItem $result
     * @param AbstractItem $item
     * @return OrderItem
     */
    public function afterConvert(ToOrderItem $subject, OrderItem $result, AbstractItem $item): OrderItem
    {
        $pointsSpent = $item->getData(EntityInterface::POINTS_SPENT);

        if ($pointsSpent) {
            $result->setData(EntityInterface::POINTS_SPENT, $pointsSpent);
        }

        return $result;
    }
}
