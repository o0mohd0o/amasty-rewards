<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Quote\Validator;

use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\Config\Source\RedemptionLimitTypes;
use Amasty\Rewards\Model\Quote\SpendingChecker;
use Magento\Quote\Model\Quote;

class LimitValidator implements ValidatorInterface
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var SpendingChecker
     */
    private $spendingChecker;

    public function __construct(
        Config $configProvider,
        SpendingChecker $spendingChecker
    ) {
        $this->configProvider = $configProvider;
        $this->spendingChecker = $spendingChecker;
    }

    /**
     * @param Quote $quote
     * @param float|int|string $usedPoints
     * @param array &$pointsData
     */
    public function validate(Quote $quote, $usedPoints, array &$pointsData): void
    {
        $storeId = (int)$quote->getStoreId();
        $pointsData['allowed_points'] = $usedPoints;
        $isEnableLimit = (int)$this->configProvider->isEnableLimit($storeId);

        if ($isEnableLimit === RedemptionLimitTypes::LIMIT_AMOUNT) {
            $limitAmount = $this->configProvider->getRewardAmountLimit($storeId);

            if ($usedPoints > $limitAmount) {
                $pointsData['allowed_points'] = $limitAmount;
                $pointsData['notice'] =
                    __('Number of redeemed reward points cannot exceed %1 for this order.', $limitAmount);
            }
        } elseif ($isEnableLimit === RedemptionLimitTypes::LIMIT_PERCENT) {
            $limitPercent = $this->configProvider->getRewardPercentLimit($storeId);
            $useSubtotalInclTax = $this->configProvider->isUseSubtotalInclTax($storeId);

            $subtotal = 0;

            foreach ($quote->getAllItems() as $item) {
                if ($this->spendingChecker->isPossibleSpendOnItem($item)) {
                    $subtotal += $useSubtotalInclTax ? $item->getRowTotalInclTax() : $item->getRowTotal();
                }
            }

            $allowedPercent = round(($subtotal / 100 * $limitPercent) / $quote->getBaseToQuoteRate(), 2);
            $rate = $this->configProvider->getPointsRate($storeId);
            $basePoints = $usedPoints / $rate;

            if ($basePoints > $allowedPercent) {
                $pointsData['allowed_points'] = $allowedPercent * $rate;
                $pointsData['notice'] =
                    __(
                        'Number of redeemed reward points cannot exceed %1 '
                        . '% of cart subtotal %2 tax for this order.',
                        $limitPercent,
                        $useSubtotalInclTax ? __('including') : __('excluding')
                    );
            }
        }
    }
}
