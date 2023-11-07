<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Controller\Adminhtml\Rewards;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Layout\Builder;

class Grid extends \Amasty\Rewards\Controller\Adminhtml\Rewards
{
    public const ALLOWED_HANDLES = [
        'rewards' => 'amasty_rewards_rewards_ajaxgrid',
        'expiration' => 'amasty_rewards_expiration_ajaxgrid'
    ];

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var Builder
     */
    private $builder;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        Builder $builder
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->builder = $builder;
    }

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $handleName = $this->getRequest()->getParam('name');

        if (!empty(self::ALLOWED_HANDLES[$handleName])) {
            $this->_view->getLayout()->getUpdate()->addHandle(self::ALLOWED_HANDLES[$handleName]);
        }

        $customerId = (int)$this->getRequest()->getParam('id');

        if ($customerId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        }

        $this->builder->build();

        return $resultPage;
    }
}
