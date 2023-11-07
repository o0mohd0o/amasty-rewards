<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Rewards\Widget\Grid\Renderer;

use Amasty\Rewards\Model\Config\Source\Actions as SourceActions;
use Magento\Backend\Block\Context;

class Actions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * @var SourceActions
     */
    private $actions;

    public function __construct(
        SourceActions $actions,
        Context $context,
        array $data = []
    ) {
        $this->actions = $actions;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $action = $row->getData('action');

        $actions = $this->actions->toOptionArray();

        if (isset($actions[$action])) {
            $result = $actions[$action];
        } else {
            $result = $action;
        }

        return $result;
    }
}
