<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Adminhtml\Sales\Creditmemo;

use Magento\Sales\Block\Adminhtml\Order\Creditmemo\View as CreditmemoView;
use Magento\Sales\Model\Order;

class View extends CreditmemoView
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_Sales';

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->getCreditmemo()->getOrder();
    }
}
