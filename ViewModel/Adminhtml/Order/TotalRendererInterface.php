<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\ViewModel\Adminhtml\Order;

use Magento\Sales\Model\Order;

interface TotalRendererInterface
{
    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param Order $order
     * @return string|float|null
     */
    public function getPointsValue(Order $order);
}
