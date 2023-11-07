<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Observer;

use Amasty\Rewards\Model\ReviewManagement;
use Magento\Framework\Event\ObserverInterface;

class ReviewProductMassUpdateStatus implements ObserverInterface
{
    /**
     * @var ReviewManagement
     */
    private $reviewManagement;

    public function __construct(
        ReviewManagement $reviewManagement
    ) {
        $this->reviewManagement = $reviewManagement;
    }

    /**
     * Event controller_action_postdispatch_review_product_massUpdateStatus
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $reviewsIds = $observer->getRequest()->getParam('reviews');
        $status = $observer->getRequest()->getParam('status');

        if (is_array($reviewsIds) && $status == \Magento\Review\Model\Review::STATUS_APPROVED) {
            foreach ($reviewsIds as $reviewId) {
                $this->reviewManagement->addReviewPoints($reviewId);
            }
        }
    }
}
