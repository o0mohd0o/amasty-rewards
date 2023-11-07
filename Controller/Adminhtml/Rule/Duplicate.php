<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */
/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Rewards\Controller\Adminhtml\Rule;

class Duplicate extends \Amasty\Rewards\Controller\Adminhtml\Rule
{
    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        if (!$ruleId) {
            $this->messageManager->addErrorMessage(__('Please select a rule to duplicate.'));
            return $this->_redirect('*/*');
        }
        try {
            $rule = clone $this->ruleRepository->get($ruleId);
            $rule->setIsActive(0);
            $rule->setId(null);
            $this->ruleRepository->save($rule);

            $this->messageManager
                ->addSuccessMessage(__('The rule has been duplicated. Please feel free to activate it.'));

            return $this->_redirect('*/*/edit', ['id' => $rule->getId()]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*');
            return false;
        }
    }
}
