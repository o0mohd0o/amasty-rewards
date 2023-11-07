<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */
/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Rewards\Controller\Adminhtml;

abstract class Rule extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Amasty\Rewards\Model\RuleFactory
     */
    protected $rewardsRuleFactory;

    /**
     * @var \Amasty\Rewards\Api\RuleRepositoryInterface
     */
    protected $ruleRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Rewards\Model\RuleFactory $rewardsRuleFactory,
        \Amasty\Rewards\Api\RuleRepositoryInterface $ruleRepository
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->rewardsRuleFactory = $rewardsRuleFactory;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * Determine if authorized to perform group action.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Rewards::rule');
    }
}
