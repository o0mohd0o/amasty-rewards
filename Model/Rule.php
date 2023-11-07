<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Api\Data\RuleInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * @method ResourceModel\Rule getResource()
 * @method ResourceModel\Rule _getResource()
 */
class Rule extends \Magento\Rule\Model\AbstractModel implements RuleInterface
{
    public const BEFORE_TAX = 'before_tax';
    public const AFTER_TAX = 'after_tax';
    public const CUSTOM_BEHAVIOR = 2;
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    /**
     * Constants for expiration behavior
     */
    public const EXPIRATION_DEFAULT = 0;
    public const EXPIRATION_NEVER = 1;
    public const EXPIRATION_CUSTOM = 2;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    private $combineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    private $actionFactory;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * Absolutely necessary for install sample data
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $actionFactory,
        Config $configProvider,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
        $this->combineFactory = $combineFactory;
        $this->actionFactory = $actionFactory;
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
    }

    protected function _construct()
    {
        $this->_init(\Amasty\Rewards\Model\ResourceModel\Rule::class);
        parent::_construct();
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine|\Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * @return \Magento\Rule\Model\Action\Collection|\Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->actionFactory->create();
    }

    /**
     * @param int $storeId
     * @return false|mixed
     */
    public function getStoreLabel(int $storeId)
    {
        $labels = (array)$this->getStoreLabels();

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (isset($labels[0]) && $labels[0]) {
            return $labels[0];
        }

        return false;
    }

    /**
     * @param CustomerInterface $customer
     * @return bool
     */
    public function validateByCustomer(CustomerInterface $customer): bool
    {
        return in_array($customer->getWebsiteId(), $this->getWebsiteIds())
            && in_array(
                $customer->getGroupId(),
                $this->getCustomerGroupIds()
            );
    }

    /**
     * Set if not yet and retrieve rule store labels
     *
     * @return array
     */
    public function getStoreLabels(): array
    {
        if (!$this->hasStoreLabels()) {
            $labels = $this->_getResource()->getStoreLabels($this->getId());
            $this->setStoreLabels($labels);
        }

        return (array)$this->_getData('store_labels');
    }

    /**
     * @return $this
     */
    public function activate(): Rule
    {
        $this->setIsActive(1);
        $this->save();

        return $this;
    }

    /**
     * @return $this
     */
    public function inactivate(): Rule
    {
        $this->setIsActive(0);
        $this->save();

        return $this;
    }

    /**
     * @param $action
     * @return $this
     */
    public function loadByAction($action): Rule
    {
        $this->addData($this->getResource()->loadByAction($action));

        return $this;
    }

    /**
     * Get days, after which rewards should expire
     *
     * @param string $storeId
     * @return ?int
     */
    public function getExpirationDays($storeId): ?int
    {
        if ($this->getExpirationBehavior() == self::EXPIRATION_NEVER) {
            return null;
        }

        if ($this->getExpirationBehavior() == self::EXPIRATION_CUSTOM) {
            $days = $this->getExpirationPeriod();
        } else {
            $days = $this->configProvider->getExpirationPeriod($storeId);
        }

        return $days;
    }

    /**
     * Get rule associated customer groups Ids
     *
     * @return array
     */
    public function getCustomerGroupIds(): array
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array)$customerGroupIds);
        }

        return (array)$this->_getData('customer_group_ids');
    }

    /**
     * @return int|mixed|null
     */
    public function getRuleId(): int
    {
        return (int)$this->_getData(RuleInterface::RULE_ID);
    }

    /**
     * @param int $ruleId
     * @return $this|RuleInterface
     */
    public function setRuleId(int $ruleId): RuleInterface
    {
        $this->setData(RuleInterface::RULE_ID, $ruleId);

        return $this;
    }

    /**
     * @return int
     */
    public function getIsActive(): int
    {
        return (int)$this->_getData(RuleInterface::IS_ACTIVE);
    }

    /**
     * @param int $isActive
     * @return $this|RuleInterface
     */
    public function setIsActive(int $isActive): RuleInterface
    {
        $this->setData(RuleInterface::IS_ACTIVE, $isActive);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->_getData(RuleInterface::NAME);
    }

    /**
     * @param string $name
     * @return $this|RuleInterface
     */
    public function setName(string $name): RuleInterface
    {
        $this->setData(RuleInterface::NAME, $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getConditionsSerialized(): string
    {
        return (string)$this->_getData(RuleInterface::CONDITIONS_SERIALIZED);
    }

    /**
     * @param string|null $conditionsSerialized
     * @return $this|RuleInterface
     */
    public function setConditionsSerialized(?string $conditionsSerialized): RuleInterface
    {
        $this->setData(RuleInterface::CONDITIONS_SERIALIZED, $conditionsSerialized);

        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return (string)$this->_getData(RuleInterface::ACTION);
    }

    /**
     * @param string|null $action
     * @return $this|RuleInterface
     */
    public function setAction(?string $action): RuleInterface
    {
        $this->setData(RuleInterface::ACTION, $action);

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return (float)$this->_getData(RuleInterface::AMOUNT);
    }

    /**
     * @param float $amount
     * @return $this|RuleInterface
     */
    public function setAmount(float $amount): RuleInterface
    {
        $this->setData(RuleInterface::AMOUNT, $amount);

        return $this;
    }

    /**
     * @return float
     */
    public function getSpentAmount(): float
    {
        return (float)$this->_getData(RuleInterface::SPENT_AMOUNT);
    }

    /**
     * @param float $spentAmount
     * @return $this|RuleInterface
     */
    public function setSpentAmount(float $spentAmount): RuleInterface
    {
        $this->setData(RuleInterface::SPENT_AMOUNT, $spentAmount);

        return $this;
    }

    /**
     * @return array
     */
    public function getProductsSkusArray(): array
    {
        $productsSkus = $this->getProductsSku();

        if ($productsSkus) {
            return \explode(',', $productsSkus);
        }

        return [];
    }

    /**
     * @return array
     */
    public function getCategoriesArray(): array
    {
        $categories = $this->getCategories();

        if ($categories) {
            return \explode(',', $categories);
        }

        return [];
    }

    /**
     * @return int
     */
    public function getInactiveDays(): int
    {
        return (int)$this->_getData(RuleInterface::INACTIVE_DAYS);
    }

    /**
     * @param int $days
     * @return RuleInterface|Rule
     */
    public function setInactiveDays(int $days): RuleInterface
    {
        return $this->setData(RuleInterface::INACTIVE_DAYS, $days);
    }

    /**
     * @return int
     */
    public function getRecurring(): int
    {
        return (int)$this->_getData(RuleInterface::RECURRING);
    }

    /**
     * @param int $status
     * @return RuleInterface|Rule
     */
    public function setRecurring(int $status): RuleInterface
    {
        return $this->setData(RuleInterface::RECURRING, $status);
    }

    /**
     * @param int $behavior
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setExpirationBehavior(int $behavior): RuleInterface
    {
        return $this->setData(RuleInterface::EXPIRATION_BEHAVIOR, $behavior);
    }

    /**
     * @return int
     */
    public function getExpirationBehavior(): int
    {
        return (int)$this->_getData(RuleInterface::EXPIRATION_BEHAVIOR);
    }

    /**
     * @param int $period
     *
     * @return \Amasty\Rewards\Api\Data\RuleInterface
     */
    public function setExpirationPeriod(int $period): RuleInterface
    {
        return $this->setData(RuleInterface::EXPIRATION_PERIOD, $period);
    }

    /**
     * @return int
     */
    public function getExpirationPeriod(): int
    {
        return (int)$this->_getData(RuleInterface::EXPIRATION_PERIOD);
    }

    /**
     * @param bool $isSkipdiscountedProducts
     * @return RuleInterface
     */
    public function setSkipDiscountedProducts(bool $isSkipdiscountedProducts): RuleInterface
    {
        return $this->setData(RuleInterface::SKIP_DISCOUNTED_PRODUCTS, $isSkipdiscountedProducts);
    }

    /**
     * @return bool
     */
    public function getSkipDiscountedProducts(): bool
    {
        return (bool)$this->getData(RuleInterface::SKIP_DISCOUNTED_PRODUCTS);
    }

    /**
     * @param bool $grantProductsForSpecificProducts
     * @return RuleInterface
     */
    public function setGrantPointsForSpecificProducts(bool $grantProductsForSpecificProducts): RuleInterface
    {
        return $this->setData(RuleInterface::GRANT_POINTS_FOR_SPECIFIC_PRODUCTS, $grantProductsForSpecificProducts);
    }

    /**
     * @return bool
     */
    public function getGrantPointsForSpecificProducts(): bool
    {
        return (bool)$this->getData(RuleInterface::GRANT_POINTS_FOR_SPECIFIC_PRODUCTS);
    }

    /**
     * @param int $action
     * @return RuleInterface
     */
    public function setActionForEarning(int $action): RuleInterface
    {
        return $this->setData(RuleInterface::ACTION, $action);
    }

    /**
     * @return int
     */
    public function getActionForEarning(): int
    {
        return (int)$this->getData(RuleInterface::ACTION_FOR_EARNING);
    }

    /**
     * @param string $prooductsSku
     * @return RuleInterface
     */
    public function setProductsSku(string $prooductsSku): RuleInterface
    {
        return $this->setData(RuleInterface::PRODUCTS_SKU, $prooductsSku);
    }

    /**
     * @return string
     */
    public function getProductsSku(): string
    {
        return (string)$this->getData(RuleInterface::PRODUCTS_SKU);
    }

    /**
     * @param string $categories
     * @return RuleInterface
     */
    public function setCategories(string $categories): RuleInterface
    {
        return $this->setData(RuleInterface::CATEGORIES, $categories);
    }

    /**
     * @return string
     */
    public function getCategories(): string
    {
        return (string)$this->getData(RuleInterface::CATEGORIES);
    }
}
