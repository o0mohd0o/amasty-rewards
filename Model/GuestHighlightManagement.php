<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Api\Data\HighlightInterfaceFactory;
use Amasty\Rewards\Api\GuestHighlightManagementInterface;
use Amasty\Rewards\Api\RuleRepositoryInterface;
use Amasty\Rewards\Model\Catalog\Highlight\Management;
use Amasty\Rewards\Model\Checkout\HighlightManagement;
use Amasty\Rewards\Model\Config\Source\Actions;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Store\Model\StoreManagerInterface;

class GuestHighlightManagement implements GuestHighlightManagementInterface
{
    public const REGISTRATION_RULE_MESSAGE = 0;
    public const ALL_RULES_MESSAGE = 1;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var HighlightInterfaceFactory
     */
    private $highlightFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var HighlightManagement
     */
    private $highlightManagement;

    /**
     * @var Management
     */
    private $catalogManagement;

    public function __construct(
        Config $config,
        RuleRepositoryInterface $ruleRepository,
        HighlightInterfaceFactory $highlightFactory,
        StoreManagerInterface $storeManager,
        HighlightManagement $highlightManagement,
        Management $catalogManagement
    ) {
        $this->config = $config;
        $this->ruleRepository = $ruleRepository;
        $this->highlightFactory = $highlightFactory;
        $this->storeManager = $storeManager;
        $this->highlightManagement = $highlightManagement;
        $this->catalogManagement = $catalogManagement;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHighlight($page, $productId = null, $attributes = '')
    {
        $needToChangeMessage = self::REGISTRATION_RULE_MESSAGE;
        $amount = 0;

        if (!$this->isVisible($page)) {
            return null;
        }

        switch ($page) {
            case self::PAGE_PRODUCT:
            case self::PAGE_CATEGORY:
                if ($productId) {
                    $amount = $this->catalogManagement->getAmountForProductForGuest((int)$productId, $attributes);
                }

                break;
            case self::PAGE_CART:
            case self::PAGE_CHECKOUT:
                $amount = $this->highlightManagement->calculateAmount();
                break;
        }

        if ($amount > 0) {
            $needToChangeMessage = self::ALL_RULES_MESSAGE;
        }

        $rules = $this->ruleRepository->getRulesByAction(
            Actions::REGISTRATION_ACTION,
            $this->storeManager->getStore()->getWebsiteId(),
            GroupInterface::NOT_LOGGED_IN_ID
        );

        /** @var \Amasty\Rewards\Api\Data\RuleInterface $rule */
        foreach ($rules as $rule) {
            $amount += $rule->getAmount();
        }

        /** @var \Amasty\Rewards\Api\Data\HighlightInterface $highlight */
        $highlight = $this->highlightFactory->create();
        $highlight->setVisible((bool)$amount)
            ->setCaptionColor($this->config->getHighlightColor($this->getStoreId()))
            ->setCaptionText(__('%1', $amount))
            ->setRegistrationLink($this->getRegistrationLink())
            ->setNeedToChangeMessage($needToChangeMessage);

        return $highlight;
    }

    /**
     * @inheritDoc
     */
    public function isVisible($page)
    {
        switch ($page) {
            case self::PAGE_PRODUCT:
                $isVisibleOnPage = $this->config->getHighlightProductVisibility($this->getStoreId());
                break;
            case self::PAGE_CART:
                $isVisibleOnPage = $this->config->getHighlightCartVisibility($this->getStoreId());
                break;
            case self::PAGE_CHECKOUT:
                $isVisibleOnPage = $this->config->getHighlightCheckoutVisibility($this->getStoreId());
                break;
            case self::PAGE_CATEGORY:
                $isVisibleOnPage = $this->config->getHighlightCategoryVisibility($this->getStoreId());
                break;
            default:
                throw new \InvalidArgumentException(__('Invalid page argument value.'));
        }

        return $this->config->isHighlightGuestVisibility($this->getStoreId()) && $isVisibleOnPage;
    }

    /**
     * @return mixed|string|null
     */
    public function getRegistrationLink()
    {
        if (!$this->config->isLinkToRegistrationVisible($this->getStoreId())) {
            return null;
        }

        try {
            return $this->storeManager->getStore()->getUrl('customer/account/create');
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
