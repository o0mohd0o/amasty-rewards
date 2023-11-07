<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Controller\History;

use Amasty\Rewards\Model\ConstantRegistryInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Session as CustomerSession;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @var \Amasty\Rewards\Model\ResourceModel\Rewards
     */
    private $rewardsResource;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        \Amasty\Rewards\Model\ResourceModel\Rewards $rewardsResource,
        \Amasty\Rewards\Model\Config $configProvider
    ) {
        parent::__construct($context);

        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->rewardsResource = $rewardsResource;
        $this->configProvider = $configProvider;
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->configProvider->isEnabled()) {
            return $this->_forward('noroute');
        }
        $customerId = $this->getCustomerId();

        if ($customerId && $this->customerSession->isLoggedIn()) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
            $statistic = $this->rewardsResource->getStatistic($customerId);

            $this->coreRegistry->register(ConstantRegistryInterface::CUSTOMER_STATISTICS, $statistic);

            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('My Rewards History'));
            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();
        } else {
            return $this->_redirect('customer/account/login');
        }
    }

    /**
     * Retrieve customer data object
     *
     * @return int
     */
    protected function getCustomerId(): int
    {
        return (int)$this->customerSession->getCustomerId();
    }
}
