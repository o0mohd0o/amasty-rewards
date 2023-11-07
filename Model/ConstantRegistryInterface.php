<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

interface ConstantRegistryInterface
{
    /**#@+
     * Customer Rewards Notify Attrs
     */
    public const NOTIFICATION_EARNING = 'amrewards_earning_notification';

    public const NOTIFICATION_EXPIRE = 'amrewards_expire_notification';

    public const CURRENT_REWARD = 'current_amasty_rewards_rule';

    public const FORM_NAMESPACE = 'amasty_rewards_rule_form';
    /**#@-*/

    /**
     * Key for Registry
     */
    public const CUSTOMER_STATISTICS = 'current_amasty_rewards_statistic';

    /**
     * Store ID key
     */
    public const STORE_ID = 'store_id';

    public const FORBID_EARNING = 'amrewards_forbid_earning';
}
