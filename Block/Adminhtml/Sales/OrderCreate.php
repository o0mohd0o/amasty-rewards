<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Sales;

use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;

class OrderCreate extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    private $orderCreate;

    /**
     * @var CustomerBalanceRepositoryInterface
     */
    private $customerBalanceRepository;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $configProvider;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        CustomerBalanceRepositoryInterface $customerBalanceRepository,
        \Amasty\Rewards\Model\Config $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderCreate = $orderCreate;
        $this->customerBalanceRepository = $customerBalanceRepository;
        $this->configProvider = $configProvider;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->orderCreate->getQuote();
    }

    /**
     * @return bool
     */
    public function isCanUsePoint()
    {
        return $this->getQuote()->getCustomerId() && $this->getCustomerRewardsBalance();
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->configProvider->getPointsRate($this->getStoreId());
    }

    /**
     * @return float
     */
    public function getCustomerRewardsBalance()
    {
        if (!$this->hasData('amrewards_points_balance')) {
            $points = $this->customerBalanceRepository->getBalanceByCustomerId((int)$this->getQuote()->getCustomerId());
            $this->setData('amrewards_points_balance', $points);
        }

        return $this->getData('amrewards_points_balance');
    }

    /**
     * @return float
     */
    public function getUsedRewards(): float
    {
        return (float)$this->getQuote()->getData(EntityInterface::POINTS_SPENT);
    }

    /**
     * @return int
     */
    private function getStoreId(): int
    {
        return (int)$this->getQuote()->getStoreId();
    }

    /**
     * @return bool
     */
    public function isTooltipEnabled(): bool
    {
        $storeId = $this->getStoreId();

        return $this->configProvider->isSpecificProductsEnabled($storeId)
            && $this->configProvider->isBlockTooltipEnabled($storeId);
    }

    /**
     * @return string|null
     */
    public function getTooltipText(): ?string
    {
        return $this->configProvider->getBlockTooltipText($this->getStoreId());
    }
}
