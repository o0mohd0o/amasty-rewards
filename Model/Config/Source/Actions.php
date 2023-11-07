<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Actions implements OptionSourceInterface
{
    public const ORDER_COMPLETED_ACTION = 'ordercompleted';
    public const SUBSCRIPTION_ACTION = 'subscription';
    public const BIRTHDAY_ACTION = 'birthday';
    public const MONEY_SPENT_ACTION = 'moneyspent';
    public const REGISTRATION_ACTION = 'registration';
    public const REVIEW_ACTION = 'review';
    public const VISIT_ACTION = 'visit';
    public const ADMIN_ACTION = 'admin';
    public const REWARDS_SPEND_ACTION = 'rewards_spend';
    public const REWARDS_EXPIRED_ACTION = 'rewards_expired';
    public const REFUND_ACTION = 'refund';
    public const CANCEL_ACTION = 'cancel';

    public function toOptionArray(): array
    {
        return [
            self::ORDER_COMPLETED_ACTION => __('Order Completed'),
            self::SUBSCRIPTION_ACTION => __('Newsletter subscription'),
            self::BIRTHDAY_ACTION => __('Customer birthday'),
            self::MONEY_SPENT_ACTION => __('For every $X spent'),
            self::REGISTRATION_ACTION => __('Registration'),
            self::VISIT_ACTION => __('Inactive for a long time'),
            self::REVIEW_ACTION => __('Review written'),
            self::ADMIN_ACTION => __('Admin Point Change'),
            self::REWARDS_SPEND_ACTION => __('Order Paid'),
            self::REWARDS_EXPIRED_ACTION => __('Expiration'),
            self::REFUND_ACTION => __('Refund'),
            self::CANCEL_ACTION => __('Canceled')
        ];
    }
}
