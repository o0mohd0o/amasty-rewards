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

class RefundProcessor
{
    /**
     * @var ActionInterface[]
     */
    private $actions;

    public function __construct(
        array $actions = []
    ) {
        $this->actions = $actions;
    }

    /**
     * @param Creditmemo $creditmemo
     * @throws LocalizedException
     */
    public function processPoints(Creditmemo $creditmemo): void
    {
        foreach ($this->actions as $action) {
            if ($action instanceof ActionInterface) {
                $action->execute($creditmemo);
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Modifier should implement %s interface.',
                        ActionInterface::class
                    )
                );
            }
        }
    }
}
