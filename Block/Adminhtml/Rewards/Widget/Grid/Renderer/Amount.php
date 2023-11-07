<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Rewards\Widget\Grid\Renderer;

class Amount extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $amount = $row->getData('amount');

        if ($amount > 0) {
            $amount = '+' . $amount;
        }

        return $amount;
    }
}
