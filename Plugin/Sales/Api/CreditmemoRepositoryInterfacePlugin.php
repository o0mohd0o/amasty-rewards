<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Sales\Api;

use Amasty\Rewards\Model\Order\Creditmemo\RefundProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;

class CreditmemoRepositoryInterfacePlugin
{
    /**
     * @var RefundProcessor
     */
    private $refundProcessor;

    public function __construct(
        RefundProcessor $refundProcessor
    ) {
        $this->refundProcessor = $refundProcessor;
    }

    /**
     * @param CreditmemoRepositoryInterface $subject
     * @param Creditmemo $creditmemo
     * @return Creditmemo
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(CreditmemoRepositoryInterface $subject, Creditmemo $creditmemo): Creditmemo
    {
        $this->refundProcessor->processPoints($creditmemo);

        return $creditmemo;
    }
}
