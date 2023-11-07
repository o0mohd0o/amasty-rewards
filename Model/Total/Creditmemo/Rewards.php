<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Total\Creditmemo;

use Amasty\Rewards\Model\Config;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Rewards extends AbstractTotal
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        Config $config,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($data);
        $this->config = $config;
        $this->priceCurrency = $priceCurrency;
    }

    public function collect(Creditmemo $creditmemo): self
    {
        $order = $creditmemo->getOrder();
        $refundAmount = (float)$creditmemo->getRewardPointsToRefund() - (float)$order->getAmSpentRewardPoints();

        if ($refundAmount > 0.0001) {
            $rate = $this->config->getPointsRate();
            $baseAmountToDeduct = $refundAmount / $rate;
            $amountToDeduct = $this->priceCurrency->convertAndRound(
                $baseAmountToDeduct,
                (int)$order->getStoreId(),
                (string)$order->getOrderCurrencyCode()
            );
            $baseGrandTotal = $creditmemo->getBaseGrandTotal() - $baseAmountToDeduct;
            $grandTotal = $creditmemo->getGrandTotal() - $amountToDeduct;

            if ($baseGrandTotal < 0.0001) {
                $grandTotal = $baseGrandTotal = 0;
                $creditmemo->setAllowZeroGrandTotal(true);
            }

            $creditmemo->setGrandTotal($grandTotal);
            $creditmemo->setBaseGrandTotal($baseGrandTotal);
        }

        return $this;
    }
}
