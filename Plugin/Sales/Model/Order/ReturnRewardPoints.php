<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Sales\Model\Order;

use Amasty\Rewards\Api\Data\ExpirationArgumentsInterfaceFactory;
use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Api\Data\SalesQuote\OrderInterface;
use Amasty\Rewards\Model\Order\CancelOrderProcessor;
use Magento\Sales\Model\Order;

class ReturnRewardPoints
{
    /**
     * @var CancelOrderProcessor
     */
    private $cancelOrderProcessor;

    public function __construct(
        CancelOrderProcessor $cancelOrderProcessor
    ) {
        $this->cancelOrderProcessor = $cancelOrderProcessor;
    }

    /**
     * @param Order $subject
     * @param Order $result
     * @return Order
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see Order::registerCancellation
     */
    public function afterRegisterCancellation(Order $subject, Order $result): Order
    {
        $refundedAmount = (float)$result->getData(OrderInterface::POINTS_REFUND);
        $amountToRefund = (float)$result->getData(EntityInterface::POINTS_SPENT) - $refundedAmount;

        if ($amountToRefund > 0 && $result->getCustomerId()) {
            $this->cancelOrderProcessor->refundUsedRewards($result, $amountToRefund);
            $result->setData(OrderInterface::POINTS_REFUND, $amountToRefund + $refundedAmount);
        }

        return $result;
    }
}
