<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Controller\Adminhtml\Rule;

class Index extends \Amasty\Rewards\Controller\Adminhtml\Rule
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Rewards::rule');
        $resultPage->getConfig()->getTitle()->prepend(__('Reward Points Earning Rules'));
        $resultPage->addBreadcrumb(__('Reward Points Earning Rules'), __('Reward Points Earning Rules'));

        return $resultPage;
    }
}
