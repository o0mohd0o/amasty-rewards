<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Edit\Tab\View;

use Magento\Backend\Block\Widget\Form\Generic;
use Amasty\Rewards\Model\ConstantRegistryInterface as Constant;

class SubscriptionSettings extends Generic
{
    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        array $data = []
    ) {
        $this->customerRegistry = $customerRegistry;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomer()
    {
        $customerId = (int)$this->getRequest()->getParam('id');

        return $this->customerRegistry->retrieve($customerId);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(['data' => ['id' => 'notification_options']]);
        $form->setUseContainer($this->getUseContainer());

        $fieldset = $form->addFieldset('notification_fieldset', ['legend' => __('Notification Options')]);

        $enableEarningNotification = (bool)$this->getCustomer()->getAmrewardsEarningNotification();
        $enableExpireNotification = (bool)$this->getCustomer()->getAmrewardsExpireNotification();

        $fieldset->addField(
            Constant::NOTIFICATION_EARNING,
            'checkbox',
            [
                'name' => Constant::NOTIFICATION_EARNING,
                'label' => __('Receive emails when reward points are added to the balance'),
                'checked' => $enableEarningNotification,
                'disabled' => !(bool)$enableEarningNotification
            ]
        );

        $fieldset->addField(
            Constant::NOTIFICATION_EXPIRE,
            'checkbox',
            [
                'name' => Constant::NOTIFICATION_EXPIRE,
                'label' => __('Receive emails when reward points are about to expire'),
                'checked' => $enableExpireNotification,
                'disabled' => !$enableExpireNotification
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
