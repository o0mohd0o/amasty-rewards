<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Rewards\Widget\Grid\Renderer;

use Amasty\Rewards\Api\Data\RewardsInterface;

class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    public function render(\Magento\Framework\DataObject $row)
    {
        if (!$date = $row->getData(RewardsInterface::EXPIRATION_DATE)) {
            return __('Not Expiring');
        }

        return $this->formatDate($date);
    }
}
