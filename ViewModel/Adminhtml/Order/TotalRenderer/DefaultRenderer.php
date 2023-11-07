<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\ViewModel\Adminhtml\Order\TotalRenderer;

use Amasty\Rewards\Model\Flag;
use Amasty\Rewards\ViewModel\Adminhtml\Order\TotalRendererInterface;
use Magento\Framework\Phrase;
use Magento\Sales\Model\Order;

class DefaultRenderer implements TotalRendererInterface
{
    /**
     * @var Flag
     */
    private $flag;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $valueIndex;

    public function __construct(
        Flag $flag,
        string $label = '',
        string $valueIndex = ''
    ) {
        $this->flag = $flag;
        $this->label = $label;
        $this->valueIndex = $valueIndex;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param Order $order
     * @return string|float|null|Phrase
     */
    public function getPointsValue(Order $order)
    {
        $amount = (float)$order->getData($this->valueIndex);

        if ($amount) {
            $amount = round($amount, 2);
        }

        if (!$amount
            && ($order->getEntityId() <= $this->flag->getLastOldOrderId())
        ) {
            $amount = __('N/A');
        }

        return $amount;
    }
}
