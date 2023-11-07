<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Calculation\Discount;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\StoreManagerInterface;

class Applier
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
    }

    /**
     * @param AbstractItem $item
     * @param Total $total
     * @param int|float $discount
     * @param int|float $rate
     */
    public function apply(AbstractItem $item, Total $total, float $discount, float $rate): void
    {
        $item->setData(EntityInterface::POINTS_SPENT, $discount * $rate);
        
        if (!$discount) {
            return;
        }

        $item->setBaseDiscountAmount($item->getBaseDiscountAmount() + $discount);
        $discountAmount = $this->priceCurrency->convert($discount, $this->storeManager->getStore());
        $item->setDiscountAmount($item->getDiscountAmount() + $discountAmount);
        $total->addTotalAmount('discount', -$discountAmount);
        $total->addBaseTotalAmount('discount', -$discount);
    }

    /**
     * @param AbstractItem $item
     */
    public function clear(AbstractItem $item): void
    {
        $item->setData(EntityInterface::POINTS_SPENT, null);
    }
}
