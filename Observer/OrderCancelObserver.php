<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Observer;

use Amasty\Rewards\Api;
use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Amasty\Rewards\Model\Config\Source\Actions;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderCancelObserver implements ObserverInterface
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
     * Return reward points
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): OrderCancelObserver
    {
        /** @var RewardsInterface $modelRewards */
        $modelRewards = $this->rewardsRepository->getEmptyModel();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $amount = (float)$order->getData(Api\Data\SalesQuote\EntityInterface::POINTS_SPENT);
        if ($order->getCustomerId() && $amount) {
            $modelRewards->setCustomerId((int)$order->getCustomerId());
            $modelRewards->setAmount($amount);
            if ($modelRewards->getCustomerId() && $modelRewards->getAmount()) {
                $modelRewards->setComment(__('Order #%1 Canceled', $order->getIncrementId())->render());

                $modelRewards->setAction(Actions::CANCEL_ACTION);
                $this->rewardsProvider->addRewardPoints($modelRewards, (int)$order->getStoreId());
            }
        }

        return $this;
    }
}
