<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;

class RewardsPropertyProvider
{
    public const STORE_ID = 'store_id';
    public const CUSTOMER_GROUP_ID = 'customer_group_id';

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var CustomerBalanceRepositoryInterface
     */
    private $customerBalanceRepository;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $configProvider;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\UrlInterface $urlBuilder,
        CustomerBalanceRepositoryInterface $customerBalanceRepository,
        \Amasty\Rewards\Model\Config $configProvider
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->session = $session;
        $this->customerBalanceRepository = $customerBalanceRepository;
        $this->configProvider = $configProvider;
    }

    /**
     * @return array
     */
    public function getRewardsData(): array
    {
        $customerId = $this->customerSession->getCustomerId();
        $pointsUsed = number_format(
            (float)$this->session->getQuote()->getData(EntityInterface::POINTS_SPENT),
            2,
            '.',
            ''
        );
        $pointsLeft = $this->customerBalanceRepository->getBalanceByCustomerId((int)$customerId) - $pointsUsed;

        if (!$pointsUsed) {
            $pointsUsed = 0;
        }

        return [
            'customerId' => $customerId,
            'pointsUsed' => $pointsUsed,
            'pointsLeft' => $pointsLeft,
            'pointsRate' => $this->getCurrencyPointsRate(),
            'currentCurrencyCode' => $this->storeManager->getStore()->getCurrentCurrency()->getCurrencyCode(),
            'rateForCurrency' => $this->configProvider->getPointsRate($this->getStoreId()),
            'applyUrl' => $this->urlBuilder->getUrl('amrewards/index/rewardPost'),
            'cancelUrl' => $this->urlBuilder->getUrl(
                'amrewards/index/rewardPost',
                [
                    'remove' => 1,
                ]
            ),
            'minimumPointsValue' => $this->configProvider->getMinPointsRequirement($this->getStoreId()),
            'isRewardsTooltipEnabled' => $this->isTooltipEnabled(),
            'rewardsTooltipContent' => $this->getTooltipText()
        ];
    }

    /**
     * @return float
     */
    public function getCurrencyPointsRate()
    {
        $baseCurrency = $this->storeManager->getStore()->getBaseCurrency();
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();

        return round((float)$baseCurrency->getRate($currentCurrencyCode), 3);
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return (int)$this->storeManager->getStore()->getId();
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
