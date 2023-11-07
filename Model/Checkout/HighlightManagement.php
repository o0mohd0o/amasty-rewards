<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Checkout;

use Amasty\Rewards\Api\CheckoutHighlightManagementInterface;
use Amasty\Rewards\Api\Data\HighlightInterface;
use Amasty\Rewards\Api\Data\HighlightInterfaceFactory;
use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Api\RuleRepositoryInterface;
use Amasty\Rewards\Model\Calculation\Earning;
use Amasty\Rewards\Model\CheckoutRewardsManagement;
use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\Config\Source\Actions;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Api\CartManagementInterface;
use Psr\Log\LoggerInterface;

class HighlightManagement extends AbstractSimpleObject implements CheckoutHighlightManagementInterface
{
    /**
     * @var Config
     */
    private $rewardsConfig;

    /**
     * @var Earning
     */
    private $calculator;

    /**
     * @var CheckoutSessionFactory
     */
    private $checkoutSessionFactory;

    /**
     * @var CustomerSessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var float|null
     */
    private $amount = null;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var HighlightInterfaceFactory
     */
    private $highlightFactory;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutRewardsManagement
     */
    private $checkoutRewardsManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Config $rewardsConfig,
        Earning $calculator,
        CheckoutSessionFactory $checkoutSessionFactory,
        CustomerSessionFactory $customerSessionFactory,
        RuleRepositoryInterface $ruleRepository,
        CartManagementInterface $cartManagement,
        HighlightInterfaceFactory $highlightFactory,
        CheckoutSession $checkoutSession = null, // TODO move to not optional
        CustomerSession $customerSession = null,// TODO move to not optional
        CheckoutRewardsManagement $checkoutRewardsManagement = null, // TODO move to not optional
        LoggerInterface $logger = null // TODO move to not optional
    ) {
        $this->rewardsConfig = $rewardsConfig;
        $this->calculator = $calculator;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->ruleRepository = $ruleRepository;
        $this->cartManagement = $cartManagement;
        $this->highlightFactory = $highlightFactory;
        $this->checkoutSession = $checkoutSession ?? ObjectManager::getInstance()->get(CheckoutSession::class);
        $this->customerSession = $customerSession ?? ObjectManager::getInstance()->get(CustomerSession::class);
        $this->checkoutRewardsManagement = $checkoutRewardsManagement
            ?? ObjectManager::getInstance()->get(CheckoutRewardsManagement::class);
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getHighlightData()
    {
        $sessionQuoteId = $this->checkoutSession->getQuote()->getId();

        if (!$this->customerSession->isLoggedIn()) {
            try {
                $this->checkoutRewardsManagement->remove($sessionQuoteId);
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }

        $storeId = $this->getQuote()->getStoreId();

        return [
            'highlight' => [
                HighlightInterface::VISIBLE => $this->canShow(),
                HighlightInterface::CAPTION_COLOR => $this->rewardsConfig->getHighlightColor($storeId),
                HighlightInterface::CAPTION_TEXT => __('%1', $this->amount)
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fillData()
    {
        /** @var HighlightInterface $highlight */
        $highlight = $this->highlightFactory->create();
        $highlight->setData($this->getHighlightData()['highlight']);

        return $highlight;
    }

    /**
     * @inheritdoc
     */
    public function getHighlightByCustomerId($customerId)
    {
        $this->quote = $this->cartManagement->getCartForCustomer($customerId);
        $hide = $this->rewardsConfig->isDisabledEarningByRewards($this->getQuote()->getStoreId())
            && (float)$this->getQuote()->getData(EntityInterface::POINTS_SPENT);

        if ($hide) {
            $this->amount = 0;
        }

        $this->calculateAmount();

        $hide = !$this->amount && !$hide;

        /** @var HighlightInterface $highlight */
        $highlight = $this->highlightFactory->create();
        $highlight->setCaptionColor($this->rewardsConfig->getHighlightColor($this->getQuote()->getStoreId()));
        $highlight->setVisible(!$hide);
        $highlight->setCaptionText(__('%1', $this->amount));

        return $highlight;
    }

    /**
     * Check customer is logged in website,
     * rewards can be earned by this order
     * customer is not forbidden to earn rewards
     *
     * @return bool
     */
    private function canShow(): bool
    {
        if (((float)$this->getQuote()->getData(EntityInterface::POINTS_SPENT)
            && $this->rewardsConfig->isDisabledEarningByRewards($this->getQuote()->getStoreId()))
            || $this->customerSessionFactory->create()->getCustomer()->getAmrewardsForbidEarning()
        ) {
            $this->amount = 0;
        }

        return (bool) $this->calculateAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible($page)
    {
        switch ($page) {
            case self::CART:
                $result = $this->rewardsConfig->getHighlightCartVisibility($this->getQuote()->getStoreId());
                break;
            case self::CHECKOUT:
                $result = $this->rewardsConfig->getHighlightCheckoutVisibility($this->getQuote()->getStoreId());
                break;
            default:
                $result = false;
                break;
        }

        return $this->customerSessionFactory->create()->isLoggedIn() && $result;
    }

    /**
     * Calculate amount by MONEY_SPENT_ACTION and ORDER_COMPLETED_ACTION rules.
     *
     * @return float|null
     */
    public function calculateAmount()
    {
        if ($this->amount === null) {
            $amount = 0;
            $website = $this->getQuote()->getStore()->getWebsiteId();
            $customerGroup = $this->getQuote()->getCustomerGroupId()
                ?: $this->customerSessionFactory->create()->getCustomerGroupId();

            $rules = $this->ruleRepository->getRulesByAction(Actions::MONEY_SPENT_ACTION, $website, $customerGroup);
            $address = $this->getAddress();

            if (!$address->getTotalQty()) {
                $address->setTotalQty($this->getQuote()->getItemsQty());
            }
            /** @var \Amasty\Rewards\Api\Data\RuleInterface $rule */
            foreach ($rules as $rule) {
                if ($rule->validate($address)) {
                    $amount += $this->calculator->calculateByAddress($address, $rule);
                }
            }

            $rules = $this->ruleRepository->getRulesByAction(Actions::ORDER_COMPLETED_ACTION, $website, $customerGroup);

            /** @var \Amasty\Rewards\Api\Data\RuleInterface $rule */
            foreach ($rules as $rule) {
                if ($rule->validate($address)) {
                    $amount += $rule->getAmount();
                }
            }

            $this->amount = floatval(round($amount, 2));
        }

        return $this->amount;
    }

    /**
     * Return address from quote to use in calculation.
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    private function getAddress()
    {
        if ($this->getQuote()->isVirtual()) {
            $address = $this->getQuote()->getBillingAddress();
        } else {
            $address = $this->getQuote()->getShippingAddress();
        }

        return $address;
    }

    /**
     * Return current quote from session.
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote()
    {
        if (!$this->quote) {
            /** @var \Magento\Checkout\Model\Session $checkoutSession */
            $checkoutSession = $this->checkoutSessionFactory->create();

            $this->quote = $checkoutSession->getQuote();
        }

        return $this->quote;
    }
}
