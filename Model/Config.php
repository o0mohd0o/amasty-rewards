<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Magento\Store\Model\ScopeInterface;

class Config
{
    public const REWARDS_SECTION = 'amrewards/';

    public const CALCULATION_GROUP = 'calculation/';
    public const NOTIFICATION_GROUP = 'notification/';
    public const POINTS_GROUP = 'points/';
    public const HIGHLIGHT_GROUP = 'highlight/';
    public const GENERAL_GROUP = 'general/';
    public const CUSTOMER_GROUP = 'customer/';
    public const REWARD_PROGRAM_RESTRICTION = 'reward_program_restriction/';
    public const SPENDING_CONFIG = 'spending_config/';
    public const ORDER_GROUP = 'order/';

    public const ENABLED = 'enabled';
    public const TAX_FIELD = 'before_after_tax';
    public const ENABLE_LIMIT_FIELD = 'enable_limit';
    public const BIRTHDAY_OFFSET = 'days';
    public const POINTS_RATE = 'rate';
    public const POINTS_ROUND_RULE = 'round_rule';
    public const DISABLE_REWARD = 'disable_reward';
    public const MINIMUM_REWARDS = 'minimum_reward';
    public const ADMIN_ACTION_NAME = 'adminaction';
    public const LIMIT_AMOUNT_REWARD_FIELD = 'limit_amount_reward';
    public const LIMIT_PERCENT_REWARD_FIELD = 'limit_percent_reward';
    public const LIMIT_INCLUDE_TAX = 'limit_include_tax';
    public const EARN_TEMPLATE = 'balance_earn_template';
    public const EMAIL_SENDER = 'email_sender';
    public const EARN_NOTICE = 'send_earn_notification';
    public const EARN_NOTIFICATION_SUBSCRIBE_BY_DEFAULT = 'subscribe_by_default_to_earn_notifications';
    public const EXPIRE_NOTICE = 'send_expire_notification';
    public const EXPIRE_NOTIFICATION_SUBSCRIBE_BY_DEFAULT = 'subscribe_by_default_to_expire_notifications';
    public const EXPIRE_SEND_DAYS = 'expiry_day_send';
    public const EXPIRE_TEMPLATE = 'points_expiring_template';
    public const EXPIRATION_BEHAVIOR = 'expiration_behavior';
    public const EXPIRATION_PERIOD = 'expiration_period';
    public const CART = 'cart';
    public const CHECKOUT = 'checkout';
    public const COLOR = 'color';
    public const PRODUCT = 'product';
    public const CATEGORY = 'category';
    public const GUEST = 'guest';
    public const DESCRIPTION_ENABLE = 'description_enable';
    public const DESCRIPTION_MESSAGE = 'description_message';
    public const SHOW_BALANCE = 'show_balance';
    public const BALANCE_LABEL = 'balance_label';
    public const RESTRICTION_MESSAGE_ENABLED = 'message_enabled';
    public const RESTRICTION_MESSAGE_TEXT = 'message_text';
    public const SPECIFIC_PRODUCTS_ENABLED = 'specific_products_enabled';
    public const PRODUCTS_ACTION = 'action';
    public const PRODUCTS_SKU = 'sku';
    public const PRODUCTS_CATEGORY = 'category';
    public const BLOCK_TOOLTIP_ENABLED = 'block_tooltip_enabled';
    public const BLOCK_TOOLTIP_TEXT = 'block_tooltip_text';
    public const READ_ONLY = 'read_only';
    public const SHOW_DETAILED = 'show_detailed';
    public const REGISTRATION_LINK = 'registration_link';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $group
     * @param string $path
     * @param string|null $store
     *
     * @return string
     */
    private function getScopeValue($group, $path, $store = null)
    {
        return $this->config->getValue(
            self::REWARDS_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string $group
     * @param string $path
     * @param int|null $store
     *
     * @return bool
     */
    private function isSetFlag($group, $path, $store = null)
    {
        return $this->config->isSetFlag(
            self::REWARDS_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is Module Enabled
     *
     * @param int|null $store
     *
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return $this->isSetFlag(self::GENERAL_GROUP, self::ENABLED, $store);
    }

    /**
     * Return true, when rewards cannot be earned by order using reward points.
     *
     * @param string $store
     *
     * @return bool
     */
    public function isDisabledEarningByRewards($store)
    {
        return (bool)$this->getScopeValue(self::POINTS_GROUP, self::DISABLE_REWARD, $store);
    }

    /**
     * @return mixed
     */
    public function getEarningCalculationMode()
    {
        return $this->getScopeValue(self::CALCULATION_GROUP, self::TAX_FIELD);
    }

    /**
     * @param string $store
     *
     * @return mixed
     */
    public function isEnableLimit($store)
    {
        return $this->getScopeValue(self::POINTS_GROUP, self::ENABLE_LIMIT_FIELD, $store);
    }

    /**
     * @param int|string|null $store
     * @return bool
     */
    public function isUseSubtotalInclTax($store = null): bool
    {
        return (bool)$this->getScopeValue(self::POINTS_GROUP, self::LIMIT_INCLUDE_TAX, $store);
    }

    /**
     * @param string $store
     *
     * @return mixed
     */
    public function getRewardAmountLimit($store)
    {
        return $this->getScopeValue(self::POINTS_GROUP, self::LIMIT_AMOUNT_REWARD_FIELD, $store);
    }

    /**
     * @param string $store
     *
     * @return mixed
     */
    public function getRewardPercentLimit($store)
    {
        return $this->getScopeValue(self::POINTS_GROUP, self::LIMIT_PERCENT_REWARD_FIELD, $store);
    }

    /**
     * @param $store
     *
     * @return string
     */
    public function getEarnTemplate($store)
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::EARN_TEMPLATE, $store);
    }

    /**
     * @param $store
     *
     * @return string
     */
    public function getEmailSender($store)
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::EMAIL_SENDER, $store);
    }

    /**
     * @param $store
     *
     * @return int
     */
    public function getSendEarnNotification($store)
    {
        return (int)$this->getScopeValue(self::NOTIFICATION_GROUP, self::EARN_NOTICE, $store);
    }

    public function canSubscribeByDefaultToEarnNotifications(?int $websiteId = null): bool
    {
        return $this->config->isSetFlag(
            self::REWARDS_SECTION . self::NOTIFICATION_GROUP . self::EARN_NOTIFICATION_SUBSCRIBE_BY_DEFAULT,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * @param $store
     *
     * @return int
     */
    public function getSendExpireNotification($store)
    {
        return (int)$this->getScopeValue(self::NOTIFICATION_GROUP, self::EXPIRE_NOTICE, $store);
    }

    public function canSubscribeByDefaultToExpireNotifications(?int $websiteId = null): bool
    {
        return $this->config->isSetFlag(
            self::REWARDS_SECTION . self::NOTIFICATION_GROUP . self::EXPIRE_NOTIFICATION_SUBSCRIBE_BY_DEFAULT,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * @param $store
     *
     * @return int
     */
    public function getExpireTemplate($store)
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::EXPIRE_TEMPLATE, $store);
    }

    /**
     * @param string|null $store
     * @return int[]|null
     */
    public function getExpireDaysToSend(string $store = null): ?array
    {
        $value = $this->getScopeValue(self::NOTIFICATION_GROUP, self::EXPIRE_SEND_DAYS, $store);
        if (empty($value)) {
            return null;
        }

        $days = array_unique(explode(',', $value));

        $result = [];
        foreach ($days as $day) {
            $result[] = (int) trim($day);
        }
        return $result;
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getExpirationBehavior($store)
    {
        return $this->getScopeValue(self::POINTS_GROUP, self::EXPIRATION_BEHAVIOR, $store);
    }

    /**
     * @param string $store
     * @return ?int
     */
    public function getExpirationPeriod($store): ?int
    {
        $days = null;

        if ($this->getExpirationBehavior($store)) {
            $days = (int)$this->getScopeValue(self::POINTS_GROUP, self::EXPIRATION_PERIOD, $store);
        }

        return $days;
    }

    /**
     * @param string $store
     *
     * @return float
     */
    public function getPointsRate($store = null): float
    {
        return (float)$this->getScopeValue(self::POINTS_GROUP, self::POINTS_RATE, $store);
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getRoundRule($store)
    {
        return $this->getScopeValue(self::POINTS_GROUP, self::POINTS_ROUND_RULE, $store);
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getStoreLocale($store)
    {
        return $this->config->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    /**
     * @return int
     */
    public function getBirthdayOffset()
    {
        return (int)$this->getScopeValue(self::GENERAL_GROUP, self::BIRTHDAY_OFFSET);
    }

    /**
     * @return string
     */
    public function getAdminActionName()
    {
        return $this->getScopeValue(self::GENERAL_GROUP, self::ADMIN_ACTION_NAME);
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getMinPointsRequirement($store)
    {
        return $this->getScopeValue(self::POINTS_GROUP, self::MINIMUM_REWARDS, $store);
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getHighlightCartVisibility($store)
    {
        return $this->getScopeValue(self::HIGHLIGHT_GROUP, self::CART, $store);
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getHighlightCheckoutVisibility($store)
    {
        return $this->getScopeValue(self::HIGHLIGHT_GROUP, self::CHECKOUT, $store);
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getHighlightProductVisibility($store)
    {
        return $this->getScopeValue(self::HIGHLIGHT_GROUP, self::PRODUCT, $store);
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getHighlightCategoryVisibility($store)
    {
        return $this->getScopeValue(self::HIGHLIGHT_GROUP, self::CATEGORY, $store);
    }

    /**
     * @param string|null $store
     *
     * @return bool
     */
    public function isHighlightGuestVisibility($store = null)
    {
        return (bool)$this->getScopeValue(self::HIGHLIGHT_GROUP, self::GUEST, $store);
    }

    /**
     * @param $store
     * @return bool
     */
    public function isLinkToRegistrationVisible($store): bool
    {
        return (bool)$this->getScopeValue(self::HIGHLIGHT_GROUP, self::REGISTRATION_LINK, $store);
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getHighlightColor($store)
    {
        return $this->getScopeValue(self::HIGHLIGHT_GROUP, self::COLOR, $store);
    }

    /**
     * @param string $store
     *
     * @return string|null
     */
    public function getRewardsPointsDescription($store)
    {
        if ($this->isSetFlag(self::CUSTOMER_GROUP, self::DESCRIPTION_ENABLE, $store)) {
            return $this->getScopeValue(self::CUSTOMER_GROUP, self::DESCRIPTION_MESSAGE, $store);
        }

        return null;
    }

    /**
     * @param string $store
     *
     * @return string|null
     */
    public function isRewardsBalanceVisible($store)
    {
        return $this->isSetFlag(self::CUSTOMER_GROUP, self::SHOW_BALANCE, $store);
    }

    /**
     * @param string $store
     *
     * @return string|null
     */
    public function getBalanceLabel($store)
    {
        return $this->getScopeValue(self::CUSTOMER_GROUP, self::BALANCE_LABEL, $store);
    }

    /**
     * @param string|null $website
     * @return bool
     */
    public function isRestrictionMessageEnabled(?string $website = null): bool
    {
        return $this->config->isSetFlag(
            self::REWARDS_SECTION . self::REWARD_PROGRAM_RESTRICTION . self::RESTRICTION_MESSAGE_ENABLED,
            ScopeInterface::SCOPE_WEBSITES,
            $website
        );
    }

    /**
     * @param string|null $website
     * @return string|null
     */
    public function getRestrictionMessageText(?string $website = null): ?string
    {
        return $this->config->getValue(
            self::REWARDS_SECTION . self::REWARD_PROGRAM_RESTRICTION . self::RESTRICTION_MESSAGE_TEXT,
            ScopeInterface::SCOPE_WEBSITES,
            $website
        );
    }

    /**
     * @param int|null $store
     * @return bool
     */
    public function isSpecificProductsEnabled(int $store = null): bool
    {
        return $this->isSetFlag(self::SPENDING_CONFIG, self::SPECIFIC_PRODUCTS_ENABLED, $store);
    }

    /**
     * @param int|null $store
     * @return int
     */
    public function getSpecificProductsAction(int $store = null): int
    {
        return (int)$this->getScopeValue(self::SPENDING_CONFIG, self::PRODUCTS_ACTION, $store);
    }

    /**
     * @param int|null $store
     * @return array
     */
    public function getProductsSku(int $store = null): array
    {
        $stringOfSku = $this->getScopeValue(self::SPENDING_CONFIG, self::PRODUCTS_SKU, $store);

        return $stringOfSku ? array_map('trim', explode(',', $stringOfSku)) : [];
    }

    /**
     * @param int|null $store
     * @return array
     */
    public function getProductsCategory(int $store = null): array
    {
        $stringOfCategory = $this->getScopeValue(self::SPENDING_CONFIG, self::PRODUCTS_CATEGORY, $store);

        return $stringOfCategory ? array_map('trim', explode(',', $stringOfCategory)) : [];
    }

    /**
     * @param int|null $store
     * @return bool
     */
    public function isBlockTooltipEnabled(int $store = null): bool
    {
        return $this->isSetFlag(self::SPENDING_CONFIG, self::BLOCK_TOOLTIP_ENABLED, $store);
    }

    /**
     * @param int|null $store
     * @return string|null
     */
    public function getBlockTooltipText(int $store = null): ?string
    {
        return $this->getScopeValue(self::SPENDING_CONFIG, self::BLOCK_TOOLTIP_TEXT, $store);
    }

    /**
     * @param int|null $store
     * @return bool
     */
    public function isReadOnlyCreditMemoFields(int $store = null): bool
    {
        return (bool)$this->isSetFlag(self::POINTS_GROUP, self::READ_ONLY, $store);
    }

    /**
     * @param int|null $store
     * @return bool
     */
    public function isNeedToShowOrderDetailed(int $store = null): bool
    {
        return (bool)$this->isSetFlag(self::ORDER_GROUP, self::SHOW_DETAILED, $store);
    }
}
