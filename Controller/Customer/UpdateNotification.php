<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Amasty\Rewards\Model\ConstantRegistryInterface as Constant;

class UpdateNotification extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $customer = $this->customerSession->getCustomer();

        if ($customer->getId()) {
            $customer->setAmrewardsEarningNotification($this->getRequest()->getParam('subscribe_earning'));
            $customer->setAmrewardsExpireNotification($this->getRequest()->getParam('subscribe_expire'));

            $customer->getResource()->saveAttribute($customer, Constant::NOTIFICATION_EARNING);
            $customer->getResource()->saveAttribute($customer, Constant::NOTIFICATION_EXPIRE);

            $this->messageManager->addSuccess(__('You saved the notification options.'));
        }

        $this->_redirect('amrewards');
    }
}
