<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;

class Editor extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var WysiwygConfig
     */
    private $wysiwygConfig;

    public function __construct(
        Context $context,
        WysiwygConfig $wysiwygConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->wysiwygConfig = $wysiwygConfig;
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setWysiwyg(true);

        $config = $this->wysiwygConfig->getConfig($element);

        $config->setData('add_variables', false)
            ->setData('add_widgets', false)
            ->setData('plugins', [])
            ->setData('add_images', false)
            ->setData('no_display', true)
            ->setData('use_container', false);
        $element->setConfig($config);

        return parent::_getElementHtml($element);
    }
}
