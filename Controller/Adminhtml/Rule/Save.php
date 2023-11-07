<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Controller\Adminhtml\Rule;

class Save extends \Amasty\Rewards\Controller\Adminhtml\Rule
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Rewards\Model\RuleFactory $rewardsRuleFactory,
        \Amasty\Rewards\Api\RuleRepositoryInterface $ruleRepository,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $coreRegistry, $rewardsRuleFactory, $ruleRepository);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();
            $rewardRuleId = (int)$this->getRequest()->getParam('id');

            try {
                /** @var \Amasty\Rewards\Model\Rule $model */
                $model = $this->rewardsRuleFactory->create();

                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                unset($data['rule']);
                if (!empty($data['categories'])) {
                    $data['categories'] = implode(',', $data['categories']);
                }

                $model->setId($rewardRuleId);
                $model->addData($data);
                $model->loadPost($data);

                $this->ruleRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $this->dataPersistor->clear(\Amasty\Rewards\Model\ConstantRegistryInterface::FORM_NAMESPACE);
                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                }
                return $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->dataPersistor->set(\Amasty\Rewards\Model\ConstantRegistryInterface::FORM_NAMESPACE, $data);
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($rewardRuleId) {
                    return $this->_redirect('*/*/edit', ['id' => $rewardRuleId]);
                }

                return $this->_redirect('*/*/new');
            }
        }

        return $this->_redirect('*/*/');
    }
}
