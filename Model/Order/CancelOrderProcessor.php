<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Order;

use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Amasty\Rewards\Model\Config\Source\Actions;
use Magento\Sales\Model\Order;

class CancelOrderProcessor
{
    /**
     * @var RewardsProviderInterface
     */
    private $rewardsProvider;

    /**
     * @var RewardsRepositoryInterface
     */
    private $rewardsRepository;

    public function __construct(
        RewardsProviderInterface $rewardsProvider,
        RewardsRepositoryInterface $rewardsRepository
    ) {
        $this->rewardsProvider = $rewardsProvider;
        $this->rewardsRepository = $rewardsRepository;
    }

    /**
     * @param Order $order
     * @param float $amount
     */
    public function refundUsedRewards(Order $order, float $amount): void
    {
        $modelRewards = $this->rewardsRepository->getEmptyModel();
        $modelRewards->setCustomerId((int)$order->getCustomerId());
        $modelRewards->setAmount($amount);
        $modelRewards->setComment(__('Order #%1 Canceled', $order->getIncrementId())->render());
        $modelRewards->setAction(Actions::CANCEL_ACTION);
        $this->rewardsProvider->addRewardPoints($modelRewards, (int)$order->getStoreId());
    }
}
