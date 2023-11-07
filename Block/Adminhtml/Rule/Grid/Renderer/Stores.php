<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */
/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Rewards\Block\Adminhtml\Rule\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Store\Model\System\Store;

class Stores extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @var Store
     */
    private $systemStore;

    public function __construct(
        Store $systemStore,
        Context $context,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $stores = $row->getData('stores');
        if (!$stores) {
            return __('Restricts in All');
        }

        $html = '';
        $data = $this->systemStore->getStoresStructure(false, explode(',', $stores));
        foreach ($data as $website) {
            $html .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $html .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
                foreach ($group['children'] as $store) {
                    $html .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
                }
            }
        }
        return $html;
    }
}
