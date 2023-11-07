<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IncludeExclude implements OptionSourceInterface
{
    /**
     * Value which equal Include for IncludeExclude dropdown.
     */
    public const INCLUDE_VALUE = 1;

    /**
     * Value which equal Exclude for IncludeExclude dropdown.
     */
    public const EXCLUDE_VALUE = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::INCLUDE_VALUE, 'label' => __('Include')],
            ['value' => self::EXCLUDE_VALUE, 'label' => __('Exclude')]
        ];
    }
}
