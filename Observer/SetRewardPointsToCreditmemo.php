<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Observer;

use Amasty\Rewards\Block\Adminhtml\Sales\Creditmemo as CreditmemoBlock;
use Magento\Framework\Event\ObserverInterface;

class SetRewardPointsToCreditmemo implements ObserverInterface
{
    /**
     * Set reward points balance to creditmemo before register
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $input = $observer->getEvent()->getInput();
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if (!empty($input[CreditmemoBlock::REFUND_KEY]) || !empty($input[CreditmemoBlock::EARNED_POINTS_KEY])) {
            $refundPoints = (float)$input[CreditmemoBlock::REFUND_KEY];
            $earnedPoints = (float)$input[CreditmemoBlock::EARNED_POINTS_KEY];
            if ($refundPoints) {
                $creditmemo->setData(CreditmemoBlock::REFUND_KEY, $refundPoints);
                $creditmemo->setAllowZeroGrandTotal(true);
            }
            if ($earnedPoints) {
                $creditmemo->setData(CreditmemoBlock::EARNED_POINTS_KEY, $earnedPoints);
            }
        }

        return $this;
    }
}
