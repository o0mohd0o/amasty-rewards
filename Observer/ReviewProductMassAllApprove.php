<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Observer;

use Amasty\Rewards\Model\ReviewManagement;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ReviewProductMassAllApprove implements ObserverInterface
{
    /**
     * @var ReviewManagement
     */
    private $reviewManagement;

    public function __construct(ReviewManagement $reviewManagement)
    {
        $this->reviewManagement = $reviewManagement;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $reviewsIds = $observer->getRequest()->getParam('reviews');

        if (is_array($reviewsIds)) {
            foreach ($reviewsIds as $reviewId) {
                $this->reviewManagement->addReviewPoints($reviewId);
            }
        }
    }
}
