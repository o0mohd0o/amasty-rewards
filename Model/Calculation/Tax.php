<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Calculation;

use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\Rule;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Tax\Model\Config as TaxConfig;

class Tax
{
    /**
     * @var Config
     */
    private $rewardsConfig;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TaxCalculation
     */
    private $taxCalculation;

    public function __construct(
        Config $rewardsConfig,
        TaxConfig $taxConfig,
        StoreManagerInterface $storeManager,
        TaxCalculation $taxCalculation
    ) {
        $this->rewardsConfig = $rewardsConfig;
        $this->taxConfig = $taxConfig;
        $this->storeManager = $storeManager;
        $this->taxCalculation = $taxCalculation;
    }

    public function correctAmountByTax(ProductInterface $product, float $amount, int $customerId): float
    {
        $addressRequestObject = $this->getAddressRequestObject($product, $customerId);
        $taxPercent = (float)$this->taxCalculation->getRate($addressRequestObject);

        $calculationMode = $this->rewardsConfig->getEarningCalculationMode();

        if ($this->taxConfig->priceIncludesTax() && $this->isSameRateAsStore($taxPercent, $addressRequestObject)) {
            if ($calculationMode === Rule::BEFORE_TAX) {
                $amount /= (1 + ($taxPercent / 100));
            }
        } else {
            if ($calculationMode === Rule::AFTER_TAX) {
                $amount *= (1 + ($taxPercent / 100));
            }
        }

        return $amount;
    }

    /**
     * @param ProductInterface $product
     * @param int $customerId
     * @return DataObject
     * @throws NoSuchEntityException
     */
    public function getAddressRequestObject(ProductInterface $product, int $customerId): DataObject
    {
        $addressRequestObject = $this->taxCalculation->getRateRequest(
            null,
            null,
            null,
            $this->storeManager->getStore(),
            $customerId
        );
        $addressRequestObject->setProductClassId($product->getTaxClassId());

        return $addressRequestObject;
    }

    protected function isSameRateAsStore(float $rate, DataObject $addressRequestObject): bool
    {
        if ((bool)$this->taxConfig->crossBorderTradeEnabled($this->storeManager->getStore())) {
            return true;
        }

        $storeRate = (float)$this->taxCalculation->getStoreRate($addressRequestObject, $this->storeManager->getStore());

        return (abs($rate - $storeRate) < 0.00001);
    }
}
