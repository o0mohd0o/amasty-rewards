<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\ViewModel\Adminhtml\Order;

use Amasty\Rewards\Model\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class View implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TotalRendererInterface[]
     */
    private $totals;

    public function __construct(
        Config $config,
        array $totals = []
    ) {
        $this->config = $config;
        $this->totals = $totals;
    }

    /**
     * @return bool
     */
    public function isNeedToDisplay(): bool
    {
        return $this->config->isNeedToShowOrderDetailed();
    }

    /**
     * @return TotalRendererInterface[]
     */
    public function getPointsTotals(): array
    {
        return $this->totals;
    }
}
