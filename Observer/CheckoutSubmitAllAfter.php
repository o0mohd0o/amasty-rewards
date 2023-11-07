<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Observer;

use Amasty\Base\Model\MagentoVersion;
use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\RewardsProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckoutSubmitAllAfter implements ObserverInterface
{
    private const PAYPAL_PLACE_ORDER_EVENT = 'paypal_express_place_order_success';

    /**
     * @var RewardsProviderInterface
     */
    private $rewardsProvider;

    /**
     * @var RewardsRepositoryInterface
     */
    private $rewardsRepository;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        RewardsProviderInterface $rewardsProvider,
        RewardsRepositoryInterface $rewardsRepository,
        MagentoVersion $magentoVersion
    ) {
        $this->rewardsProvider = $rewardsProvider;
        $this->rewardsRepository = $rewardsRepository;
        $this->magentoVersion = $magentoVersion;
    }

    public function execute(Observer $observer): void
    {
        /* Since m2.4.6 PayPal has 2 events when place order.
           But we need to execute code only once.
           @see \Magento\Paypal\Controller\Express\OnAuthorization::execute() */
        if (($observer->getEvent()->getName() === self::PAYPAL_PLACE_ORDER_EVENT)
            && (version_compare($this->magentoVersion->get(), '2.4.6', '>='))) {
            return;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        if ($quote->getCustomerId()) {
            /** @var RewardsInterface $modelRewards */
            $modelRewards = $this->rewardsRepository->getEmptyModel();
            $modelRewards->setCustomerId((int)$quote->getCustomerId());

            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();
            $comment = '';
            if (!$order) {
                $orders = $observer->getEvent()->getOrders();
                foreach ($orders as $order) {
                    $comment .= __('Order #%1', $order->getRealOrderId())->render();
                }
            } else {
                $comment = __('Order #%1', $order->getRealOrderId())->render();
            }
            $modelRewards->setAmount((float)$quote->getData(EntityInterface::POINTS_SPENT));
            $modelRewards->setComment($comment);
            $modelRewards->setAction(Actions::REWARDS_SPEND_ACTION);
            $modelRewards->setVisibleForCustomer(RewardsProvider::EVERYTIME_VISIBLE_FOR_CUSTOMER);
            if ($modelRewards->getAmount() > 0) {
                $this->rewardsProvider->deductRewardPoints($modelRewards);
            }
        }
    }
}
