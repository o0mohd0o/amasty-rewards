<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api\Data\SalesQuote;

interface OrderInterface extends EntityInterface
{
    public const POINTS_REFUND = 'am_refund_reward_points';
    public const POINTS_DEDUCT = 'am_deduct_reward_points';
    public const ORDER_PROCESSED_ATTRIBUTE = 'amrewards_order_processed';
    public const ORDER_PROCESSED_STATUS = "1";
}
