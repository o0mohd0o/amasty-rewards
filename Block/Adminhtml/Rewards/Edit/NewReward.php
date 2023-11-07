<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Rewards\Edit;

use Amasty\Rewards\Api\Data\RewardsInterface;
use Magento\Customer\Controller\RegistryConstants;

class NewReward extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->setUseContainer(true);
    }

    /**
     * Form preparation
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(['data' => ['id' => 'new_rewards_form', 'class' => 'admin__scope-old']]);
        $form->setUseContainer($this->getUseContainer());

        $customerId = (int)$this->getRequest()->getParam('id');

        $fieldset = $form->addFieldset('new_rewards_form_fieldset', []);

        $fieldset->addField('new_rewards_messages', 'note', []);

        $fieldset->addField(
            'new_rewards_action',
            'select',
            [
                'label' => __('Action'),
                'title' => __('Action'),
                'required' => true,
                'name' => 'new_rewards_action',
                'options' => [
                    'add' => __('Add'),
                    'deduct' => __('Deduct')
                ],
            ]
        );

        $fieldset->addField(
            'new_rewards_amount',
            'text',
            [
                'label' => __('Amount'),
                'title' => __('Amount'),
                'class' => 'validate-number validate-greater-than-zero',
                'required' => true,
                'name' => 'new_rewards_amount'
            ]
        );

        $fieldset->addField(
            'new_rewards_expiration_behavior',
            'select',
            [
                'label' => __('Points expiration behavior'),
                'title' => __('Points expiration behavior'),
                'required' => true,
                'name' => 'new_rewards_expiration_behavior',
                'options' => [
                    '0' => __('Never expire'),
                    '1' => __('Expire')
                ],
            ]
        );

        $fieldset->addField(
            'new_rewards_expiration_period',
            'text',
            [
                'label' => __('Points expiration period, days'),
                'title' => __('Points expiration period, days'),
                'required' => true,
                'class' => 'validate-number validate-zero-or-greater',
                'name' => 'new_rewards_expiration_period',
                'note' => __('If 0 is set, points will expire same day at midnight (12:00 am) your server time.')
            ]
        );

        $fieldset->addField(
            'new_rewards_comment',
            'textarea',
            [
                'label' => __('Comment'),
                'title' => __('Comment'),
                'required' => true,
                'name' => 'new_rewards_comment',
            ]
        );

        $fieldset->addField(
            RewardsInterface::VISIBLE_FOR_CUSTOMER,
            'checkbox',
            [
                'label' => __('Visible For Customer'),
                'title' => __('Visible For Customer'),
                'required' => false,
                'name' => RewardsInterface::VISIBLE_FOR_CUSTOMER,
                'data-form-part' => $this->getData('target_form'),
                'value' => 'false',
                'onchange' => 'this.value = this.checked;',
            ]
        );

        $fieldset->addField(
            'new_rewards_customer',
            'hidden',
            [
                'name' => 'new_rewards_comment',
                'value' => $customerId
            ]
        );

        $this->setForm($form);
    }

    /**
     * @return string
     */
    public function getAfterElementHtml()
    {
        return '<script type ="text/x-magento-init">
            {
                "#add-points": {
                    "Amasty_Rewards/js/add-points-dialog": {
                        "saveCategoryUrl":"' . $this->getUrl('amasty_rewards/rewards/new') . '",
                        "customerId":"' . $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID) . '"
                    }
                }
            }
        </script>';
    }
}
