<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Order\Creditmemo\Action;

use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Api\Data\SalesQuote\OrderInterface;
use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Amasty\Rewards\Block\Adminhtml\Sales\Creditmemo as CreditmemoBlock;
use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\Order\Creditmemo\ActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\OrderRepository;

class Refund implements ActionInterface
{
    /**
     * @var RewardsProviderInterface
     */
    private $rewardsProvider;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var RewardsRepositoryInterface
     */
    private $rewardsRepository;

    public function __construct(
        RewardsProviderInterface $rewardsProvider,
        OrderRepository $orderRepository,
        RewardsRepositoryInterface $rewardsRepository
    ) {
        $this->rewardsProvider = $rewardsProvider;
        $this->orderRepository = $orderRepository;
        $this->rewardsRepository = $rewardsRepository;
    }

    /**
     * @param Creditmemo $creditmemo
     */
    public function execute(Creditmemo $creditmemo): void
    {
        $amount = (float)$creditmemo->getData(CreditmemoBlock::REFUND_KEY);

        if ($amount) {
            /** @var RewardsInterface $modelRewards */
            $modelRewards = $this->rewardsRepository->getEmptyModel();

            $order = $creditmemo->getOrder();
            $modelRewards->setCustomerId((int)$order->getCustomerId());
            $modelRewards->setAmount($amount);
            $modelRewards->setAction(Actions::REFUND_ACTION);
            $modelRewards->setComment(
                __('Refund #%1 for Order #%2', $creditmemo->getIncrementId(), $order->getIncrementId())->render()
            );

            $this->rewardsProvider->addRewardPoints($modelRewards, (int)$order->getStoreId());

            $this->updateOrderPoints($order, $amount);
        }
    }

    /**
     * @param Order $order
     * @param float $amount
     * @throws LocalizedException
     */
    private function updateOrderPoints(Order $order, float $amount): void
    {
        $currentValue = $order->getData(OrderInterface::POINTS_REFUND);
        $order->setData(OrderInterface::POINTS_REFUND, $currentValue + $amount);

        $this->orderRepository->save($order);
    }
}
