<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Api\CustomerBalanceRepositoryInterface;
use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CheckoutRewardsManagement implements \Amasty\Rewards\Api\CheckoutRewardsManagementInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $config;

    /**
     * @var CustomerBalanceRepositoryInterface
     */
    private $customerBalanceRepository;

    /**
     * @var \Amasty\Rewards\Model\Quote\Validator\CompositeValidator
     */
    private $validator;

    public function __construct(
        \Amasty\Rewards\Model\Config $config,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        CustomerBalanceRepositoryInterface $customerBalanceRepository,
        \Amasty\Rewards\Model\Quote\Validator\CompositeValidator $validator
    ) {
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
        $this->customerBalanceRepository = $customerBalanceRepository;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $usedPoints)
    {
        if (!$usedPoints || $usedPoints < 0) {
            throw new LocalizedException(__('Points "%1" not valid.', $usedPoints));
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get((int)$cartId);
        $minPoints = $this->config->getMinPointsRequirement($quote->getStoreId());

        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        $pointsLeft = $this->customerBalanceRepository->getBalanceByCustomerId((int)$quote->getCustomerId());

        if ($minPoints && $pointsLeft < $minPoints) {
            throw new LocalizedException(
                __('You need at least %1 points to pay for the order with reward points.', $minPoints)
            );
        }

        try {
            if ($usedPoints > $pointsLeft) {
                throw new LocalizedException(__('Too much point(s) used.'));
            }

            $pointsData['notice'] = '';
            $pointsData['allowed_points'] = 0;
            $this->validator->validate($quote, $usedPoints, $pointsData);
            $usedPoints = abs($pointsData['allowed_points']);
            $itemsCount = $quote->getItemsCount();

            if ($itemsCount) {
                $this->collectCurrentTotals($quote, $usedPoints);
            }
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        $pointsData['allowed_points'] = $quote->getData(EntityInterface::POINTS_SPENT);

        if ($pointsData['allowed_points'] > 0) {
            $usedNotice = __('You used %1 point(s).', $pointsData['allowed_points']);
        } else {
            $usedNotice = __('Products in this cart can’t be covered with the reward points.');
        }

        $pointsData['notice'] = $pointsData['notice'] . ' ' . $usedNotice;

        return $pointsData;
    }

    /**
     * {@inheritdoc}
     */
    public function collectCurrentTotals(\Magento\Quote\Model\Quote $quote, $usedPoints)
    {
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->setData(EntityInterface::POINTS_SPENT, $usedPoints);
        $quote->setDataChanges(true);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        $itemsCount = $quote->getItemsCount();

        if ($itemsCount) {
            $this->collectCurrentTotals($quote, 0);
        }
    }
}
