<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Catalog\Highlight;

use Amasty\Rewards\Api\CatalogHighlightManagementInterface;
use Amasty\Rewards\Api\Data\HighlightInterface;
use Amasty\Rewards\Api\Data\HighlightInterfaceFactory;
use Amasty\Rewards\Api\RuleRepositoryInterface;
use Amasty\Rewards\Model\Calculation\Product;
use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\Config\Source\Actions;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Store\Model\StoreManagerInterface;

class Management implements CatalogHighlightManagementInterface
{
    /**
     * @var array
     */
    private $amounts = [];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Product
     */
    private $calculator;

    /**
     * @var Validator
     */
    private $highlightValidator;

    /**
     * @var HighlightInterfaceFactory
     */
    private $highlightFactory;

    /**
     * @var ValidObjectFromDataFactory
     */
    private $objectFactory;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Config $config,
        Product $calculator,
        Validator $highlightValidator,
        HighlightInterfaceFactory $highlightFactory,
        ValidObjectFromDataFactory $objectFactory,
        RuleRepositoryInterface $ruleRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->calculator = $calculator;
        $this->highlightValidator = $highlightValidator;
        $this->highlightFactory = $highlightFactory;
        $this->objectFactory = $objectFactory;
        $this->ruleRepository = $ruleRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getHighlightForProduct($productId, $customerId, $attributes = null)
    {
        $storeId = $this->storeManager->getStore()->getId();
        /** @var ValidObject $validObject */
        $validObject = $this->objectFactory->create($productId, $attributes, $customerId, $storeId);
        $pageVisibility = $this->config->getHighlightProductVisibility($storeId);
        $color = $this->config->getHighlightColor($storeId);

        return $this->commonHighlight($validObject, $pageVisibility, $color);
    }

    public function getAmountForProductForGuest(int $productId, string $attributes = ''): float
    {
        $store = $this->storeManager->getStore();
        $storeId = $store->getId();
        /** @var ValidObject $validObject */
        $validObject = $this->objectFactory->create(
            $productId,
            $attributes,
            GroupInterface::NOT_LOGGED_IN_ID,
            $storeId
        );
        $customer = $validObject->getCustomer();
        $customer->setData('customer_id', GroupInterface::NOT_LOGGED_IN_ID);
        $customer->setData('website_id', $store->getWebsiteId());

        return $this->calculateAmount($validObject);
    }

    /**
     * {@inheritdoc}
     */
    public function getHighlightForCategory($productId, $customerId, $attributes = null)
    {
        $storeId = $this->storeManager->getStore()->getId();
        /** @var ValidObject $validObject */
        $validObject = $this->objectFactory->create($productId, $attributes, $customerId, $storeId);
        $pageVisibility = $this->config->getHighlightCategoryVisibility($storeId);
        $color = $this->config->getHighlightColor($storeId);

        return $this->commonHighlight($validObject, $pageVisibility, $color);
    }

    /**
     * @param ValidObject $validObject
     * @param bool $pageVisibility
     * @param string $color
     *
     * @return HighlightInterface
     */
    private function commonHighlight($validObject, $pageVisibility, $color)
    {
        $data = [
            HighlightInterface::VISIBLE => $pageVisibility
                && $this->calculateAmount($validObject),
            HighlightInterface::CAPTION_COLOR => $color,
            HighlightInterface::CAPTION_TEXT => $this->calculateAmount($validObject)
        ];

        return $this->highlightFactory->create()->setData($data);
    }

    /**
     * @param ValidObject $validObject
     *
     * @return float
     */
    private function calculateAmount($validObject)
    {
        if (empty($this->amounts[$validObject->getProduct()->getId()])) {
            $amount = 0;

            $customer = $validObject->getCustomer();
            if ($customer->getData('customer_id') === GroupInterface::NOT_LOGGED_IN_ID) {
                $customerGroupId = GroupInterface::NOT_LOGGED_IN_ID;
            } else {
                $customerGroupId = $customer->getGroupId();
            }

            if (!$customer->getAmrewardsForbidEarning()) {
                $rules = $this->ruleRepository->getRulesByAction(
                    Actions::MONEY_SPENT_ACTION,
                    $customer->getWebsiteId(),
                    $customerGroupId
                );

                /** @var \Amasty\Rewards\Api\Data\RuleInterface $rule */
                foreach ($rules as $rule) {
                    if ($this->highlightValidator->validate($rule, $validObject)) {
                        $amount += $this->calculator->calculatePointsByProduct(
                            $validObject->getProductCandidates(),
                            (int)$customer->getId(),
                            $rule
                        );
                    }
                }
            }

            $this->amounts[$validObject->getProduct()->getId()] = floatval(round($amount, 2));
        }

        return $this->amounts[$validObject->getProduct()->getId()];
    }
}
