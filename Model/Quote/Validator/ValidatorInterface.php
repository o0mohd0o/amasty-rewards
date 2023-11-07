<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Quote\Validator;

use Magento\Quote\Model\Quote;

interface ValidatorInterface
{
    /**
     * @param Quote $quote
     * @param float|int|string $usedPoints
     * @param array &$pointsData
     */
    public function validate(Quote $quote, $usedPoints, array &$pointsData): void;
}
