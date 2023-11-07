<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Config;

class Utils
{
    /**
     * Convert comma separated string to array.
     *
     * @param string $string
     * @return array
     */
    public function convertToArray(string $string): array
    {
        $result = explode(',', $string);
        $result = array_map(function (string $value) {
            return trim($value);
        }, $result);
        $result = array_filter($result);

        return $result;
    }
}
