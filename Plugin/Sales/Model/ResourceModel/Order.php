<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */
namespace Amasty\Rewards\Plugin\Sales\Model\ResourceModel;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Api\Data\SalesQuote\OrderInterface;
use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\Order\EarningProcessor;
use Amasty\Rewards\Model\Repository\StatusHistoryRepository;
use Amasty\Rewards\Model\ResourceModel\StatusHistory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\ResourceModel\Order as SalesOrderResource;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Order
{
    /**
     * For earn rewards
     */
    public const NOT_AVAILABLE_ACTION = 'addComment';

    /**
     * @var Config
     */
    private $rewardsConfig;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var EarningProcessor
     */
    private $earningProcessor;

    /**
     * @var StatusHistoryRepository
     */
    private $statusHistoryRepository;

    /**
     * @var string
     */
    private $orderState;

    public function __construct(
        Config $rewardsConfig,
        RequestInterface $request,
        EarningProcessor $earningProcessor,
        StatusHistoryRepository $statusHistoryRepository
    ) {
        $this->rewardsConfig = $rewardsConfig;
        $this->request = $request;
        $this->earningProcessor = $earningProcessor;
        $this->statusHistoryRepository = $statusHistoryRepository;
    }

    /**
     * @param SalesOrderResource $subject
     * @param AbstractModel $order
     * @return null
     */
    public function beforeSave(SalesOrderResource $subject, AbstractModel $order)
    {
        $this->orderState = $order->getOrigData('state');

        return null;
    }

    /**
     * @param SalesOrderResource $subject
     * @param SalesOrderResource $result
     * @param AbstractModel|SalesOrder $order
     * @return SalesOrderResource
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        SalesOrderResource $subject,
        SalesOrderResource $result,
        AbstractModel $order
    ): SalesOrderResource {
        if ($this->request->getParam('creditmemo')
            || !$this->rewardsConfig->isEnabled()
            || !$this->canEarn($order)
            || ($order->getData(EntityInterface::POINTS_EARN) !== null)
        ) {
            return $result;
        }

        $this->earningProcessor->process($order);

        return $result;
    }

    /**
     * Return true if can earn rewards by MONEY_SPENT_ACTION or ORDER_COMPLETED_ACTION actions
     *
     * @param SalesOrder $orderModel
     * @return bool
     * @throws LocalizedException
     */
    private function canEarn(SalesOrder $orderModel): bool
    {
        return $orderModel->getCustomerId()
            && $this->checkOrderStateAndOrderProcessedStatus($orderModel)
            && $this->request->getActionName() !== self::NOT_AVAILABLE_ACTION
            && $this->isPossibleEarningByCustomerStatus((int)$orderModel->getCustomerId(), $orderModel->getCreatedAt())
            && !($this->rewardsConfig->isDisabledEarningByRewards($orderModel->getStoreId())
                && $orderModel->getData(EntityInterface::POINTS_SPENT))
            && !$orderModel->getTotalRefunded();
    }

    /**
     * @param int $customerId
     * @param string $creationDate
     * @return bool
     * @throws LocalizedException
     */
    private function isPossibleEarningByCustomerStatus(int $customerId, string $creationDate): bool
    {
        $result = false;
        if ($customerId) {
            $statusEntity = $this->statusHistoryRepository->getByCustomerIdAndDate($customerId, $creationDate);
            if ($statusEntity && $statusEntity->getAction() !== StatusHistory::EXCLUDE_ACTION) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @param SalesOrder $orderModel
     * @return bool
     */
    private function checkOrderStateAndOrderProcessedStatus(SalesOrder $orderModel): bool
    {
        $result = false;
        if ($orderModel->getState() === SalesOrder::STATE_COMPLETE
            && ($orderModel->getData(OrderInterface::ORDER_PROCESSED_ATTRIBUTE)
                !== OrderInterface::ORDER_PROCESSED_STATUS)
            && $this->orderState !== $orderModel->getState()
        ) {
            $result = true;
        }

        return $result;
    }
}
