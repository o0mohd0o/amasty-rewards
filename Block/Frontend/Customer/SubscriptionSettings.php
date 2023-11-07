<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Frontend\Customer;

class SubscriptionSettings extends \Magento\Framework\View\Element\Template
{
    /**
     * @var  \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\Rewards\Model\Config $config,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function showEarningCheckbox()
    {
        return $this->config->getSendEarnNotification(
            $this->storeManager->getStore($this->getCustomer()->getStoreId())
        );
    }

    /**
     * @return mixed
     */
    public function showExpireCheckbox()
    {
        return $this->config->getSendExpireNotification(
            $this->storeManager->getStore($this->getCustomer()->getStoreId())
        );
    }

    /**
     * @return bool
     */
    public function isSubscribedForEarnings()
    {
        return $this->getCustomer() ? (bool)$this->getCustomer()->getAmrewardsEarningNotification() : false;
    }

    /**
     * @return bool
     */
    public function isSubscribedForExpiring()
    {
        return $this->getCustomer() ? (bool)$this->getCustomer()->getAmrewardsExpireNotification() : false;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    private function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}
