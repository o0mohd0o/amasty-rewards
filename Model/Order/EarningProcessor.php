<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Order;

use Amasty\Rewards\Api\Data\RuleInterface;
use Amasty\Rewards\Api\Data\SalesQuote\OrderInterface;
use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Api\RuleRepositoryInterface;
use Amasty\Rewards\Model\Calculation\Earning;
use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\Order\Earning\ItemsProcessor;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Store\Model\Store;

class EarningProcessor
{
    /**
     * @var SalesOrder
     */
    private $orderModel;

    /**
     * @var Earning
     */
    private $calculator;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var RewardsProviderInterface
     */
    private $rewardsProvider;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ItemsProcessor
     */
    private $itemsProcessor;

    /**
     * @var OrderResource
     */
    private $orderResource;

    public function __construct(
        Earning $calculator,
        RuleRepositoryInterface $ruleRepository,
        RewardsProviderInterface $rewardsProvider,
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepository,
        OrderResource $orderResource,
        ItemsProcessor $itemsProcessor
    ) {
        $this->calculator = $calculator;
        $this->ruleRepository = $ruleRepository;
        $this->rewardsProvider = $rewardsProvider;
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
        $this->orderResource = $orderResource;
        $this->itemsProcessor = $itemsProcessor;
    }

    /**
     * @param SalesOrder $order
     * @throws LocalizedException
     */
    public function process(Order $order): void
    {
        $this->orderModel = $order;
        $store = $this->orderModel->getStore();
        $orderRules = $this->getOrderRules($store);
        $spentRules = $this->getSpentRules($store);
        $address = $this->getAddress();

        if ($orderRules || $spentRules) {
            $paymentMethod = $address->getPaymentMethod();
            $address->setPaymentMethod($this->orderModel->getPayment()->getMethod());
            $address->setTotalQty($this->orderModel->getTotalQtyOrdered());

            $this->actionsCombine($spentRules, $orderRules, $address, (int)$store->getId());

            if ($paymentMethod) {
                $address->setPaymentMethod($paymentMethod);
            }

            if ($this->orderModel->getAmEarnRewardPoints() !== null) {
                $this->orderRepository->save($this->orderModel);
            }
        }

        if ($this->orderModel->getState() === SalesOrder::STATE_COMPLETE
            && ($this->orderModel->getData(OrderInterface::ORDER_PROCESSED_ATTRIBUTE)
                !== OrderInterface::ORDER_PROCESSED_STATUS)) {
            $this->changeOrderProcessedAttribute($this->orderModel);
        }
    }

    /**
     * @param SalesOrder $orderModel
     * @throws CouldNotSaveException
     */
    private function changeOrderProcessedAttribute(SalesOrder $orderModel): void
    {
        $orderModel->setAmrewardsOrderProcessed(OrderInterface::ORDER_PROCESSED_STATUS);
        try {
            $this->orderResource->saveAttribute($orderModel, OrderInterface::ORDER_PROCESSED_ATTRIBUTE);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    /**
     * @param Store $store
     * @return RuleInterface[]
     */
    private function getSpentRules(Store $store): array
    {
        return $this->ruleRepository->getRulesByAction(
            Actions::MONEY_SPENT_ACTION,
            $store->getWebsiteId(),
            $this->orderModel->getCustomerGroupId()
        );
    }

    /**
     * @param Store $store
     * @return RuleInterface[]
     */
    private function getOrderRules(Store $store): array
    {
        return $this->ruleRepository->getRulesByAction(
            Actions::ORDER_COMPLETED_ACTION,
            $store->getWebsiteId(),
            $this->orderModel->getCustomerGroupId()
        );
    }

    /**
     * @param array $spentRules
     * @param array $orderRules
     * @param Address $address
     * @param int $storeId
     * @throws LocalizedException
     */
    private function actionsCombine(array $spentRules, array $orderRules, Address $address, int $storeId): void
    {
        $this->actionByOrderRules($orderRules, $address, $storeId);
        $this->actionsBySpentRules($spentRules, $address, $storeId);
    }

    /**
     * @param array $orderRules
     * @param Address $address
     * @param int $storeId
     * @throws LocalizedException
     */
    private function actionByOrderRules(array $orderRules, Address $address, int $storeId): void
    {
        foreach ($orderRules as $rule) {
            if ($rule->validate($address)) {
                $amount = $rule->getAmount();
                $this->itemsProcessor->process($this->orderModel->getAllItems(), $amount);
                $currentPoints = $this->orderModel->getAmEarnRewardPoints();
                $this->orderModel->setAmEarnRewardPoints($currentPoints + $amount);
                $this->rewardsProvider->addPointsByRule(
                    $rule,
                    $this->orderModel->getCustomerId(),
                    $storeId
                );
            }
        }
    }

    /**
     * @param array $spentRules
     * @param Address $address
     * @param int $storeId
     * @throws LocalizedException
     */
    private function actionsBySpentRules(array $spentRules, Address $address, int $storeId): void
    {
        foreach ($spentRules as $rule) {
            if ($rule->validate($address)) {
                $amount = $this->calculator->calculateByOrder($this->orderModel, $rule);

                if ($amount) {
                    $currentPoints = $this->orderModel->getAmEarnRewardPoints();
                    $this->orderModel->setAmEarnRewardPoints($currentPoints + $amount);
                    $this->rewardsProvider->addPointsByRule(
                        $rule,
                        $this->orderModel->getCustomerId(),
                        $storeId,
                        $amount
                    );
                }
            }
        }
    }

    /**
     * IMPORTANT: Do not use @see \Magento\Quote\Api\CartRepositoryInterface because
     * cart repository can load only quotes from current store
     *
     * @return Address
     */
    private function getAddress(): Address
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        /** @var Quote $quote */
        $quote = $collection
            ->addFieldToFilter('entity_id', $this->orderModel->getQuoteId())
            ->setPageSize(1)
            ->getFirstItem();

        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        return $address;
    }
}
