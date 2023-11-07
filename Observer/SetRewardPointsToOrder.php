<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Observer;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SetRewardPointsToOrder implements ObserverInterface
{
    /**
     * Event 'sales_model_service_quote_submit_before'
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        $pointsSpent = $quote->getData(EntityInterface::POINTS_SPENT);

        if ($pointsSpent) {
            $order->setData(EntityInterface::POINTS_SPENT, $pointsSpent);
        }
    }
}
