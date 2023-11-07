<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api\Data;

use Magento\Customer\Api\Data\CustomerInterface;

interface RuleInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const RULE_ID = 'rule_id';
    public const IS_ACTIVE = 'is_active';
    public const NAME = 'name';
    public const CONDITIONS_SERIALIZED = 'conditions_serialized';
    public const ACTION = 'action';
    public const AMOUNT = 'amount';
    public const SPENT_AMOUNT = 'spent_amount';
    public const INACTIVE_DAYS = 'inactive_days';
    public const RECURRING = 'recurring';
    public const EXPIRATION_BEHAVIOR = 'expiration_behavior';
    public const EXPIRATION_PERIOD = 'expiration_period';
    public const SKIP_DISCOUNTED_PRODUCTS = 'skip_discounted_products';
    public const GRANT_POINTS_FOR_SPECIFIC_PRODUCTS = 'grant_points_for_specific_products';
    public const ACTION_FOR_EARNING = 'action_for_earning';
    public const PRODUCTS_SKU = 'products_sku';
    public const CATEGORIES = 'categories';
    /**#@-*/

    /**
     * Validate customer by his website and his group
     *
     * @param CustomerInterface $customer
     *
     * @return bool
     */
    public function validateByCustomer(CustomerInterface $customer): bool;

    /**
     * @return int
     */
    public function getRuleId(): int;

    /**
     * @param int $ruleId
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setRuleId(int $ruleId): RuleInterface;

    /**
     * @return int
     */
    public function getIsActive(): int;

    /**
     * @param int $isActive
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setIsActive(int $isActive): RuleInterface;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $name
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setName(string $name): RuleInterface;

    /**
     * @return string|null
     */
    public function getConditionsSerialized(): ?string;

    /**
     * @param string|null $conditionsSerialized
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setConditionsSerialized(?string $conditionsSerialized): RuleInterface;

    /**
     * @return string|null
     */
    public function getAction(): ?string;

    /**
     * @param string|null $action
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setAction(?string $action): RuleInterface;

    /**
     * @return float
     */
    public function getAmount(): float;

    /**
     * @param float $amount
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setAmount(float $amount): RuleInterface;

    /**
     * @return float
     */
    public function getSpentAmount(): float;

    /**
     * @param float $spentAmount
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setSpentAmount(float $spentAmount): RuleInterface;

    /**
     * @return int
     */
    public function getInactiveDays(): int;

    /**
     * @param int $days
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setInactiveDays(int $days): RuleInterface;

    /**
     * @return int
     */
    public function getRecurring(): int;

    /**
     * @param int $status
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setRecurring(int $status): RuleInterface;

    /**
     * @param int $behavior
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setExpirationBehavior(int $behavior): RuleInterface;

    /**
     * @return int
     */
    public function getExpirationBehavior(): int;

    /**
     * @param int $period
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setExpirationPeriod(int $period): RuleInterface;

    /**
     * @return int
     */
    public function getExpirationPeriod(): int;

    /**
     * Get Rule label by specified store
     *
     * @param int $storeId
     *
     * @return string|bool
     */
    public function getStoreLabel(int $storeId);

    /**
     * @param bool $isSkipdiscountedProducts
     * @return RuleInterface
     */
    public function setSkipDiscountedProducts(bool $isSkipdiscountedProducts): RuleInterface;

    /**
     * @return bool
     */
    public function getSkipDiscountedProducts(): bool;

    /**
     * @param bool $grantProductsForSpecificProducts
     * @return RuleInterface
     */
    public function setGrantPointsForSpecificProducts(bool $grantProductsForSpecificProducts): RuleInterface;

    /**
     * @return bool
     */
    public function getGrantPointsForSpecificProducts(): bool;

    /**
     * @param int $action
     * @return RuleInterface
     */
    public function setActionForEarning(int $action): RuleInterface;

    /**
     * @return int
     */
    public function getActionForEarning(): int;

    /**
     * @param string $prooductsSku
     * @return RuleInterface
     */
    public function setProductsSku(string $prooductsSku): RuleInterface;

    /**
     * @return string
     */
    public function getProductsSku(): string;

    /**
     * @return mixed
     */
    public function getProductsSkusArray(): array;

    /**
     * @return mixed
     */
    public function getCategoriesArray(): array;

    /**
     * @param string $categories
     * @return RuleInterface
     */
    public function setCategories(string $categories): RuleInterface;

    /**
     * @return string
     */
    public function getCategories(): string;
}
