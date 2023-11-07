<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Sales;

use Amasty\Base\Model\Serializer;
use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\Config;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo as OrderCreditmemo;

class Creditmemo extends Template
{
    public const REFUND_KEY = 'reward_points_to_refund';

    public const EARNED_POINTS_KEY = 'earned_points';

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var CustomerBalanceRepositoryInterface
     */
    private $customerBalanceRepository;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Context $context,
        Registry $registry,
        Config $configProvider,
        Serializer $serializer,
        CustomerBalanceRepositoryInterface $customerBalanceRepository,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
        $this->customerBalanceRepository = $customerBalanceRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return OrderCreditmemo
     */
    public function getCreditmemo(): OrderCreditmemo
    {
        return $this->coreRegistry->registry('current_creditmemo');
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->getCreditmemo()->getOrder();
    }

    /**
     * @return bool
     */
    public function canRefundRewardPoints(): bool
    {
        $order = $this->getCreditmemo()->getOrder();

        if ($order->getCustomerIsGuest()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getTooltipConfig(): string
    {
        $tooltipConfig = [
            'tooltip' => [
                'trigger' => '[data-tooltip-trigger=rewards_tooltip]',
                'action' => 'click',
                'delay' => 0,
                'track' => false,
                'position' => 'top'
            ]
        ];

        return str_replace('"', "'", $this->serializer->serialize($tooltipConfig));
    }

    /**
     * @return float
     */
    public function getCustomerRewardsBalance(): float
    {
        return $this->customerBalanceRepository->getBalanceByCustomerId((int)$this->getOrder()->getCustomerId());
    }

    /**
     * @return float
     */
    public function getCustomerDeductPoints(): float
    {
        if (!$this->hasData(self::EARNED_POINTS_KEY)) {
            $this->setData(self::EARNED_POINTS_KEY, $this->calculatePoints(EntityInterface::POINTS_EARN));
        }

        return (float)$this->getData(self::EARNED_POINTS_KEY);
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return (float)$this->configProvider->getPointsRate($this->getCreditmemo()->getStoreId());
    }

    /**
     * @return float
     */
    public function getRefundRewardPointsBalance(): float
    {
        if (!$this->hasData(self::REFUND_KEY)) {
            $amount = $this->calculatePoints(EntityInterface::POINTS_SPENT);
            $this->setData(self::REFUND_KEY, $amount);
        }

        return (float)$this->getData(self::REFUND_KEY);
    }

    /**
     * @return bool
     */
    public function isReadOnlyFields(): bool
    {
        return $this->configProvider->isReadOnlyCreditMemoFields((int)$this->getCreditmemo()->getStoreId());
    }

    /**
     * @param string $pointsKey
     * @return float
     */
    private function calculatePoints(string $pointsKey): float
    {
        $orderItems = $this->getCreditmemo()->getOrder()->getItems();
        $points = 0;

        /** @var CreditmemoItemInterface $item */
        foreach ((array)$this->getCreditmemo()->getItems() as $item) {
            $orderItem = $orderItems[$item->getOrderItemId()] ?? null;

            if ($orderItem && $item->getQty()) {
                $pointsToRefund = $orderItem->getData($pointsKey);
                $points += ($item->getQty() / $orderItem->getQtyOrdered()) * $pointsToRefund;
            }
        }

        return round($points, 2);
    }
}
