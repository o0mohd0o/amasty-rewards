<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Controller\Adminhtml\Rewards;

use Magento\Customer\Controller\RegistryConstants;
use Amasty\Rewards\Model\ConstantRegistryInterface;

class Index extends \Amasty\Rewards\Controller\Adminhtml\Rewards
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * @var \Amasty\Rewards\Model\ResourceModel\Rewards
     */
    private $rewardsResource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Amasty\Rewards\Model\ResourceModel\Rewards $rewardsResource
    ) {
        parent::__construct($context);

        $this->coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->rewardsResource = $rewardsResource;
    }

    public function execute()
    {
        $customerId = (int)$this->getRequest()->getParam('id');

        if ($customerId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        } else {
            $this->messageManager->addWarningMessage(__('Customer is not found.'));

            return $this->_redirect('admin/dashboard');
        }

        $statistic = $this->rewardsResource->getStatistic($customerId);

        $this->coreRegistry->register(ConstantRegistryInterface::CUSTOMER_STATISTICS, $statistic);

        $resultLayout = $this->resultLayoutFactory->create();

        return $resultLayout;
    }
}
