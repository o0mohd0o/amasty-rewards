<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Frontend;

use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Amasty\Rewards\Model\Config as ConfigProvider;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\View\Element\Template;

class Balance extends Template
{
    /**
     * @var CustomerSessionFactory
     */
    private $sessionFactory;

    /**
     * @var CustomerBalanceRepositoryInterface
     */
    private $customerBalanceRepository;

    /**
     * @var ConfigProvider
     */
    private $config;

    public function __construct(
        Template\Context $context,
        CustomerSessionFactory $sessionFactory,
        CustomerBalanceRepositoryInterface $customerBalanceRepository,
        ConfigProvider $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->sessionFactory = $sessionFactory;
        $this->customerBalanceRepository = $customerBalanceRepository;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        if (!$this->hasData('customer_id')) {
            $customerSession = $this->sessionFactory->create();
            $this->setData('customer_id', $customerSession->getCustomerId());
            $this->setData('is_logged_in', $customerSession->isLoggedIn());
        }

        return $this->getData('customer_id')
            && ($this->getData('is_logged_in') === true)
            && $this->config->isEnabled($this->_storeManager->getStore()->getId())
            && $this->config->isRewardsBalanceVisible($this->_storeManager->getStore()->getId());
    }

    /**
     * @return float
     */
    public function getCustomerBalance()
    {
        return $this->customerBalanceRepository->getBalanceByCustomerId((int)$this->getData('customer_id'));
    }

    /**
     * @return string|null
     */
    public function getBalanceLabel()
    {
        return $this->config->getBalanceLabel($this->_storeManager->getStore()->getId());
    }

    public function getTemplate()
    {
        if (!$this->isVisible()) {
            return '';
        }

        return parent::getTemplate();
    }
}
