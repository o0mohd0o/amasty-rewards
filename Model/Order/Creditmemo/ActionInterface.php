<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Order\Creditmemo;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Creditmemo;

interface ActionInterface
{
    /**
     * @param Creditmemo $creditmemo
     * @throws LocalizedException
     */
    public function execute(Creditmemo $creditmemo): void;
}
