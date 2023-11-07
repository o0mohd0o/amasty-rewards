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

use Amasty\Rewards\Model\ResourceModel\Rule\CollectionFactory;
use Psr\Log\LoggerInterface;

class MassAction extends \Amasty\Rewards\Controller\Adminhtml\Rule
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Rewards\Model\RuleFactory $rewardsRuleFactory,
        \Amasty\Rewards\Api\RuleRepositoryInterface $ruleRepository,
        CollectionFactory $ruleCollection,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $ruleCollection;
        $this->logger = $logger;
        parent::__construct($context, $registry, $rewardsRuleFactory, $ruleRepository);
    }

    public function execute()
    {
        $ids = $this->getRequest()->getParam('rules');
        $action = $this->getRequest()->getParam('action');
        if ($ids && in_array($action, ['activate', 'inactivate', 'delete'])) {
            try {
                /**
                 * @var $collection \Amasty\Rewards\Model\ResourceModel\Rule\Collection
                 */
                $collection = $this->collectionFactory->create();

                $collection->addFieldToFilter('rule_id', ['in'=>$ids]);
                $collection->walk($action);
                switch ($action) {
                    case 'activate':
                        $messageSuccess = __('You activated the rule(s).');
                        break;
                    case 'inactivate':
                        $messageSuccess = __('You inactivated the rule(s).');
                        break;
                    default:
                        $messageSuccess = __('You deleted the rule(s).');
                        break;
                }
                $this->messageManager->addSuccessMessage($messageSuccess);
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete rule(s) right now. Please review the log and try again.') . $e->getMessage()
                );
                $this->logger->critical($e);
                $this->_redirect('*/*/');
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule(s) to delete.'));
        $this->_redirect('*/*/');
    }
}
