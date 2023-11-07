<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Sales\Model\Order\Convert\Order;

use Amasty\Rewards\Block\Adminhtml\Sales\Creditmemo as CreditmemoBlock;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Convert\Order;
use Magento\Sales\Model\Order\Creditmemo;

class SetRefundAmountToCreditmemo
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param Order $subject
     * @param Creditmemo $result
     * @return Creditmemo
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToCreditmemo(Order $subject, Creditmemo $result): Creditmemo
    {
        $creditmemoParams = $this->request->getParam('creditmemo');
        if ($creditmemoParams && isset($creditmemoParams[CreditmemoBlock::REFUND_KEY])) {
            $result->setRewardPointsToRefund($creditmemoParams[CreditmemoBlock::REFUND_KEY]);
        }

        return $result;
    }
}
