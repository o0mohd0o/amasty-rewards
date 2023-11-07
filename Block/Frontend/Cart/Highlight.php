<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Frontend\Cart;

use Amasty\Rewards\Api\CheckoutHighlightManagementInterface;
use Amasty\Rewards\Api\GuestHighlightManagementInterface;
use Magento\Framework\View\Element\Template;

class Highlight extends Template
{
    use \Amasty\Rewards\Model\ArrayPathTrait;

    /**
     * @var CheckoutHighlightManagementInterface
     */
    private $highlightManagement;

    /**
     * @var GuestHighlightManagementInterface
     */
    private $guestHighlightManagement;

    public function __construct(
        Template\Context $context,
        CheckoutHighlightManagementInterface $highlightManagement,
        GuestHighlightManagementInterface $guestHighlightManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->highlightManagement = $highlightManagement;
        $this->guestHighlightManagement = $guestHighlightManagement;
    }

    public function getJsLayout()
    {
        $path = 'components/amasty-rewards-highlight-cart';

        if ($this->highlightManagement->isVisible(CheckoutHighlightManagementInterface::CART)) {
            $this->setToArrayByPath(
                $this->jsLayout,
                $path,
                $this->highlightManagement->getHighlightData()
            );
        } elseif ($this->guestHighlightManagement->isVisible(GuestHighlightManagementInterface::PAGE_CART)) {
            $this->setToArrayByPath(
                $this->jsLayout,
                $path . '/component',
                'Amasty_Rewards/js/guest-highlight',
                false
            );
            $this->setToArrayByPath(
                $this->jsLayout,
                $path . '/highlight',
                $this->guestHighlightManagement
                    ->getHighlight(GuestHighlightManagementInterface::PAGE_CART)
                    ->getData()
            );
        } else {
            $this->unsetArrayValueByPath($this->jsLayout, $path);
        }

        return parent::getJsLayout();
    }
}
