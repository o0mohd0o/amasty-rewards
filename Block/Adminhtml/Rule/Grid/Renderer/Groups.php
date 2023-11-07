<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Rule\Grid\Renderer;

use Amasty\Rewards\Model\Config\Source\CustomerGroups;
use Magento\Backend\Block\Context;

class Groups extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * @var CustomerGroups
     */
    private $customerGroups;

    public function __construct(
        CustomerGroups $customerGroups,
        Context $context,
        array $data = []
    ) {
        $this->customerGroups = $customerGroups;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $groups = $row->getData('cust_groups');
        if (!$groups) {
            return __('Restricts For All');
        }
        $groups = explode(',', $groups);

        $html = '';
        foreach ($this->customerGroups->toOptionArray() as $row) {
            if (in_array($row['value'], $groups)) {
                $html .= $row['label'] . "<br />";
            }
        }

        return $html;
    }
}
