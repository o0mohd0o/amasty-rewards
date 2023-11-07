<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Reports;

use Magento\Backend\Block\Widget\Form\Generic;

class Configurator extends Generic
{
    /**#@+
     * Data range
     */
    public const LAST_DAY = 1;

    public const LAST_WEEK = 7;

    public const LAST_MONTH = 30;

    public const OVERALL = 1000;

    public const CUSTOM = 0;

    public const ALL = 'all';
    /**#@-*/

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    private $objectConverter;

    /**
     * @var \Amasty\Rewards\Model\Date
     */
    private $date;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Amasty\Rewards\Model\Date $date,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
        $this->date = $date;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rewards_reports_');
        $form->addField(
            'website',
            'select',
            [
                'label' => __('Website:'),
                'title' => __('Website:'),
                'name' => 'website',
                'class' => 'amrewards-reports-field',
                'values' => $this->getWebsitesArray()
            ]
        );

        $form->addField(
            'customer_group',
            'select',
            [
                'label' => __('Customer Group:'),
                'title' => __('Customer Group:'),
                'name' => 'customer_group',
                'class' => 'amrewards-reports-field',
                'values' => $this->getCustomerGroupsArray()
            ]
        );

        $form->addField(
            'date_range',
            'select',
            [
                'label' => __('Date Range:'),
                'title' => __('Date Range:'),
                'name' => 'date_range',
                'class' => 'amrewards-reports-field',
                'value' => self::LAST_DAY,
                'values' => $this->getDateRangeArray()
            ]
        );

        $form->addField(
            'date_from',
            'date',
            [
                'label' => __('From:'),
                'title' => __('From:'),
                'name' => 'date_from',
                'required' => true,
                'readonly' => true,
                'style' => 'display:none;',
                'class' => 'amrewards-reports-field',
                'date_format' => 'M/d/Y',
                'value' => $this->date->getDateWithOffsetByDays(-5),
                'max_date' => $this->date->convertDate($this->date->getDateWithOffsetByDays(0)),
            ]
        );

        $form->addField(
            'date_to',
            'date',
            [
                'label' => __('To:'),
                'title' => __('To:'),
                'name' => 'date_to',
                'required' => true,
                'readonly' => true,
                'style' => 'display:none;',
                'class' => 'amrewards-reports-field',
                'date_format' => 'M/d/Y',
                'value' => $this->date->getDateWithOffsetByDays(0),
                'max_date' => $this->date->convertDate($this->date->getDateWithOffsetByDays(0))
            ]
        );

        $form->addField(
            'submit',
            'button',
            [
                'value' => __('Refresh'),
                'title' => __('Refresh'),
                'name' => 'submit',
                'class' => 'amrewards-reports-button action-default scalable',
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    private function getDateRangeArray()
    {
        return [
            [
                'value' => self::LAST_DAY,
                'label' => __('Today')
            ],
            [
                'value' => self::LAST_WEEK,
                'label' => __('Last 7 days')
            ],
            [
                'value' => self::LAST_MONTH,
                'label' => __('Last 30 days')
            ],
            [
                'value' => self::OVERALL,
                'label' => __('Overall')
            ],
            [
                'value' => self::CUSTOM,
                'label' => __('Custom')
            ],
        ];
    }

    /**
     * @return array
     */
    private function getCustomerGroupsArray()
    {
        $customerGroups = $this->objectConverter->toOptionArray(
            $this->groupRepository->getList(
                $this->searchCriteriaBuilder->addFilter('customer_group_id', 0, 'neq')->create()
            )->getItems(),
            'id',
            'code'
        );

        array_unshift($customerGroups, ['value' => self::ALL, 'label' => __('All Customer Groups')]);

        return $customerGroups;
    }

    /**
     * @return array
     */
    private function getWebsitesArray()
    {
        $websites = $this->objectConverter->toOptionArray(
            $this->_storeManager->getWebsites(),
            'website_id',
            'name'
        );

        array_unshift($websites, ['value' => self::ALL, 'label' => __('All Websites')]);

        return $websites;
    }
}
