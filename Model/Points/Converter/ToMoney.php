<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Points\Converter;

use Amasty\Rewards\Model\Config;

class ToMoney
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param float $points
     * @param int $storeId
     * @param float $allItemsPrice
     * @return float
     */
    public function convert(float $points, int $storeId, float $allItemsPrice): float
    {
        $rate = $this->config->getPointsRate($storeId);
        $roundRule = $this->config->getRoundRule($storeId);
        $basePoints = $points / $rate;

        if ($allItemsPrice < $basePoints) {
            if ($roundRule === 'down') {
                $basePoints = floor($allItemsPrice);
            } else {
                $basePoints = $allItemsPrice;
            }
        }

        return (float)$basePoints;
    }
}
