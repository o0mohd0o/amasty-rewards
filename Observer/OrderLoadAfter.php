<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Observer;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Magento\Framework\Event\ObserverInterface;
use Amasty\Rewards\Model\Config as ConfigProvider;
use Magento\Sales\Model\Order;

class OrderLoadAfter implements ObserverInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Set forced can creditmemo flag if order payed fully by reward points
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$this->configProvider->isEnabled() || $order->canUnhold() || $order->isCanceled()
            || $order->getState() === Order::STATE_CLOSED
            || $order->getTotalPaid() > 0
        ) {
            return $this;
        }

        if ($order->getData(EntityInterface::POINTS_SPENT) > $order->getTotalRefunded() && $order->hasInvoices()) {
            $order->setForcedCanCreditmemo(true);
        }

        return $this;
    }
}
