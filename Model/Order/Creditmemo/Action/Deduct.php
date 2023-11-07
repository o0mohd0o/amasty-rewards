<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Order\Creditmemo\Action;

use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Api\Data\SalesQuote\OrderInterface;
use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Block\Adminhtml\Sales\Creditmemo as CreditmemoBlock;
use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\Order\Creditmemo\ActionInterface;
use Amasty\Rewards\Model\Repository\RewardsRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\OrderRepository;

class Deduct implements ActionInterface
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
     * @var RewardsRepository
     */
    private $rewardsRepository;

    /**
     * @var CustomerBalanceRepositoryInterface
     */
    private $customerBalanceRepository;

    public function __construct(
        RewardsProviderInterface $rewardsProvider,
        OrderRepository $orderRepository,
        CustomerBalanceRepositoryInterface $customerBalanceRepository,
        RewardsRepository $rewardsRepository
    ) {
        $this->rewardsProvider = $rewardsProvider;
        $this->orderRepository = $orderRepository;
        $this->customerBalanceRepository = $customerBalanceRepository;
        $this->rewardsRepository = $rewardsRepository;
    }

    /**
     * @param Creditmemo $creditmemo
     */
    public function execute(Creditmemo $creditmemo): void
    {
        /** @var RewardsInterface $modelRewards */
        $modelRewards = $this->rewardsRepository->getEmptyModel();

        $modelRewards->setAmount((float)$creditmemo->getData(CreditmemoBlock::EARNED_POINTS_KEY));

        if ($modelRewards->getAmount()) {
            $order = $creditmemo->getOrder();
            $modelRewards->setCustomerId((int)$order->getCustomerId());
            $modelRewards->setAction(Actions::REFUND_ACTION);
            $this->validatePoints($order, $modelRewards->getAmount());
            $modelRewards->setComment(
                __(
                    'Deduct Points #%1 for Order #%2',
                    $creditmemo->getIncrementId(),
                    $order->getIncrementId()
                )->render()
            );
            $customerBalance = $this->customerBalanceRepository->getBalanceByCustomerId((int)$order->getCustomerId());

            if ($customerBalance < $modelRewards->getAmount()) {
                $modelRewards->setAmount($customerBalance);
            }

            if ($modelRewards->getAmount() > 0.0001) {
                $this->rewardsProvider->deductRewardPoints($modelRewards);
                $this->updateOrderPoints($order, $modelRewards->getAmount());
            }
        }
    }

    /**
     * @param Order $order
     * @param float $amount
     * @throws LocalizedException
     * @throws \InvalidArgumentException
     */
    private function validatePoints(Order $order, float $amount): void
    {
        if ($amount <= 0) {
            throw new LocalizedException(__('You are trying to deduct negative or null amount of rewards point(s).'));
        }

        if (!$amount || !$order) {
            throw new \InvalidArgumentException('Required parameter is not set.');
        }

        $availablePoints = $order->getData(OrderInterface::POINTS_EARN)
            - $order->getData(OrderInterface::POINTS_DEDUCT);

        if ($amount > $availablePoints) {
            throw new LocalizedException(__('Too much point(s) used.'));
        }
    }

    /**
     * @param Order $order
     * @param float $amount
     */
    private function updateOrderPoints(Order $order, float $amount): void
    {
        $currentValue = $order->getData(OrderInterface::POINTS_DEDUCT);
        $order->setData(OrderInterface::POINTS_DEDUCT, $currentValue + $amount);

        $this->orderRepository->save($order);
    }
}
