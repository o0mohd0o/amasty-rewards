<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Sales\Block\Adminhtml\Order\Creditmemo\Totals;

use Magento\Sales\Block\Adminhtml\Totals;

/**
 * Creditmemo doesn't have output containers in totals section for extensions.
 */
class AddDetailedInfo
{
    private const BLOCK_NAME = 'am.reward.points.detailed';

    /**
     * @see Totals::toHtml()
     * @param Totals $subject
     * @param string $html
     * @return string
     */
    public function afterToHtml(
        Totals $subject,
        string $html
    ): string {
        $insertBlock = $subject->getChildBlock(self::BLOCK_NAME);

        if ($insertBlock) {
            $html .= $insertBlock->toHtml();
        }

        return $html;
    }
}
