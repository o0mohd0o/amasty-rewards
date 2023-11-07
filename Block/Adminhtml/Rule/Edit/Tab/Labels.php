<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Rule\Edit\Tab;

class Labels extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Block name in layout
     *
     * @var string
     */
    protected $_nameInLayout = 'store_view_labels';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metadata;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->metadata = $metadata;
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        if (version_compare($this->metadata->getVersion(), '2.2.0', '>=')) {
            //Fix for Magento >2.2.0 to display right form layout.
            //Result of compatibility with 2.1.x.
            $this->_prepareLayout();
        }

        $rule = $this->_coreRegistry->registry(\Amasty\Rewards\Model\ConstantRegistryInterface::CURRENT_REWARD);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $labels = $rule->getStoreLabels();

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset = $this->createStoreSpecificFieldset($form, $labels);
            if ($rule->isReadonly()) {
                foreach ($fieldset->getElements() as $element) {
                    $element->setReadonly(true, true);
                }
            }
        }

        return $form->toHtml();
    }

    /**
     * Create store specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $labels
     * @return \Magento\Framework\Data\Form\Element\Fieldset
     */
    protected function createStoreSpecificFieldset($form, $labels)
    {
        $fieldset = $form->addFieldset(
            'store_labels_fieldset',
            ['legend' => __('Store View Specific Comments'), 'class' => 'store-scope']
        );
        $renderer =
            $this->getLayout()->createBlock(\Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset::class);
        $fieldset->setRenderer($renderer);

        foreach ($this->_storeManager->getWebsites() as $website) {
            $fieldset->addField(
                "w_{$website->getId()}_label",
                'note',
                ['label' => $website->getName(), 'fieldset_html_class' => 'website']
            );
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField(
                    "sg_{$group->getId()}_label",
                    'note',
                    ['label' => $group->getName(), 'fieldset_html_class' => 'store-group']
                );
                foreach ($stores as $store) {
                    $fieldset->addField(
                        "s_{$store->getId()}",
                        'text',
                        [
                            'name' => 'store_labels[' . $store->getId() . ']',
                            'required' => false,
                            'label' => $store->getName(),
                            'value' => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                            'fieldset_html_class' => 'store',
                            'data-form-part' => \Amasty\Rewards\Model\ConstantRegistryInterface::FORM_NAMESPACE
                        ]
                    );
                }
            }
        }
        return $fieldset;
    }
}
