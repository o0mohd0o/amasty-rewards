<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Magento\Framework\FlagManager;

class Flag
{
    public const OLD_ORDER_ID = 'am_rewards_old_order_id';

    /**
     * @var FlagManager
     */
    private $flagManager;

    public function __construct(
        FlagManager $flagManager
    ) {
        $this->flagManager = $flagManager;
    }

    /**
     * @return int
     */
    public function getLastOldOrderId(): int
    {
        return (int)$this->flagManager->getFlagData(self::OLD_ORDER_ID);
    }
}
