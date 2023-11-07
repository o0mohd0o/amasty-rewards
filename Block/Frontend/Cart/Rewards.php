<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Frontend\Cart;

use Amasty\Rewards\Model\RewardsPropertyProvider;

/**
 * Product View block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rewards extends \Magento\Framework\View\Element\Template
{
    /**
     * @var RewardsPropertyProvider
     */
    private $rewardsPropertyProvider;

    /**
     * @var array
     */
    private $rewardsData;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        RewardsPropertyProvider $rewardsPropertyProvider,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Amasty\Rewards\Model\Config $configProvider,
        array $data
    ) {
        $this->rewardsPropertyProvider = $rewardsPropertyProvider;
        parent::__construct($context, $data);
        $this->priceCurrency = $priceCurrency;
        $this->configProvider = $configProvider;
    }

    /**
     * Retrieve customer data object
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getRewardsData()['customerId'];
    }

    /**
     * @return mixed
     */
    public function getPoints()
    {
        return $this->priceCurrency->round($this->getRewardsData()['pointsLeft']);
    }

    /**
     * @return mixed
     */
    public function getUsedPoints()
    {
        return $this->priceCurrency->round($this->getRewardsData()['pointsUsed']);
    }

    /**
     * @return float
     */
    public function getPointsRate()
    {
        return $this->getRewardsData()['pointsRate'];
    }

    /**
     * @return mixed
     */
    public function getCurrentCurrencyCode()
    {
        $currentCurrency = $this->_storeManager->getStore()->getCurrentCurrency();

        return $currentCurrency->getCurrencyCode();
    }

    /**
     * @return int
     */
    public function getRateForCurrency()
    {
        return $this->getRewardsData()['rateForCurrency'];
    }

    /**
     * @return array
     */
    private function getRewardsData()
    {
        if (!isset($this->rewardsData)) {
            $this->rewardsData = $this->rewardsPropertyProvider->getRewardsData();
        }

        return $this->rewardsData;
    }

    /**
     * @return int
     */
    public function getMinimumRewardsBalance()
    {
        $storeId = $this->getStoreId();

        return $this->priceCurrency->round($this->configProvider->getMinPointsRequirement($storeId));
    }

    /**
     * @return int
     */
    private function getStoreId(): int
    {
        return (int)$this->_storeManager->getStore()->getId();
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
