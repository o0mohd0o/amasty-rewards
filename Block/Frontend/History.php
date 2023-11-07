<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Frontend;

use Amasty\Rewards\Model\Config\Source\Actions;
use Amasty\Rewards\Model\ResourceModel\Rewards\Collection;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Theme\Block\Html\Pager;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Collection
     */
    protected $rewards;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Amasty\Rewards\Model\ResourceModel\Rewards\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\Rewards\Model\Date
     */
    private $date;

    /**
     * @var array
     */
    private $nonExpireIds = [];

    /**
     * @var \Amasty\Rewards\Model\ResourceModel\Rewards
     */
    private $rewardsResource;

    /**
     * @var Actions
     */
    private $actions;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Rewards\Model\ResourceModel\Rewards $rewardsResource,
        \Amasty\Rewards\Model\ResourceModel\Rewards\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        \Amasty\Rewards\Model\Date $date,
        Actions $actions,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->rewardsResource = $rewardsResource;
        $this->collectionFactory = $collectionFactory;
        $this->coreRegistry = $registry;
        $this->actions = $actions;
        $this->date = $date;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Rewards History'));
    }

    /**
     * @return Collection|false
     */
    public function getRewards()
    {
        if (!($customerId = $this->getCustomerId())) {
            return false;
        }

        if (!$this->rewards) {
            /** @var Collection $rewardsCollection */
            $rewardsCollection = $this->collectionFactory->create();
            $this->rewards = $rewardsCollection->addCustomerIdFilter($customerId)
                ->addExpiration($this->date->getDateWithOffsetByDays(0));
        }

        if (!$this->nonExpireIds) {
            $this->nonExpireIds = $this->rewardsResource->getNonExpireIds($this->getCustomerId());
        }

        return $this->rewards;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @param string $date
     *
     * @return string
     */
    public function formatDateByLocal($date)
    {
        $storeCode = $this->_storeManager->getStore()->getCode();

        return $this->date->convertDate($date, $storeCode);
    }

    /**
     * @param \Amasty\Rewards\Model\Rewards $reward
     *
     * @return string
     */
    public function getExpirationLabel($reward)
    {
        $label = null;

        if ($reward->getDaysLeft() !== null) {
            $label = __('expire in %1 day(s)', $reward->getDaysLeft());
        } elseif (!in_array($reward->getId(), $this->nonExpireIds)) {
            $label = __('expired');
        }

        return $label;
    }

    /**
     * @param \Amasty\Rewards\Model\Rewards $reward
     *
     * @return string
     */
    public function getExpirationLabelClass($reward)
    {
        $class = null;

        if ($reward->getDaysLeft() === null) {
            $class = '-expired';
        } elseif ($reward->getDaysLeft() > 3) {
            $class = '-warning';
        } else {
            $class = '-critical';
        }

        return $class;
    }

    /**
     * @param \Amasty\Rewards\Model\Rewards $reward
     *
     * @return bool
     */
    public function canExpire($reward)
    {
        if (in_array($reward->getId(), $this->nonExpireIds)) {
            return false;
        }

        return true;
    }

    /**
     * @param float $amount
     * @return string
     */
    public function getChangeAmount(float $amount): string
    {
        $amount = $this->convertFloatToStringWithFormat($amount);
        return (float)$amount > 0 ? '+' . $amount : $amount;
    }

    /**
     * @param float $pointLeft
     * @return string
     */
    public function getChangedPontLeft(float $pointLeft): string
    {
        return $this->convertFloatToStringWithFormat($pointLeft);
    }

    /**
     * @param float $amount
     * @return string
     */
    private function convertFloatToStringWithFormat(float $amount): string
    {
        return number_format($amount, 2);
    }

    protected function _prepareLayout(): self
    {
        parent::_prepareLayout();

        if ($this->getRewards()) {
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'amrewards.history.pager'
            )->setCollection(
                $this->getRewards()
            );
            $this->setChild('pager', $pager);
            $this->getRewards()->load();
        }

        return $this;
    }

    public function getActionName(string $action): string
    {
        $actionList = $this->actions->toOptionArray();

        if (array_key_exists($action, $actionList)) {
            $result = $actionList[$action]->getText();
        } else {
            $result = $action;
        }

        return $result;
    }
}
