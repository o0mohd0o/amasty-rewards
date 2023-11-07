<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Amasty\Rewards\Controller\Adminhtml\Rule
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Rewards\Model\RuleFactory $rewardsRuleFactory,
        \Amasty\Rewards\Api\RuleRepositoryInterface $ruleRepository,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context, $registry, $rewardsRuleFactory, $ruleRepository);
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('id');
        /** @var \Amasty\Rewards\Model\Rule $model */
        $model = $this->rewardsRuleFactory->create();

        if ($itemId) {
            $model->load($itemId);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));

                return $this->_redirect('*/*');
            }
        }

        // set entered data if was error when we do save
        $data = $this->dataPersistor->get(\Amasty\Rewards\Model\ConstantRegistryInterface::FORM_NAMESPACE);
        if (!empty($data)) {
            $model->addData($data);
        } else {
            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        }

        $this->registry->register(\Amasty\Rewards\Model\ConstantRegistryInterface::CURRENT_REWARD, $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getName()
                ? __('Edit Reward Rule `%1`', $model->getName())
            : __("Add new Reward Rule")
        );

        return $resultPage;
    }
}
