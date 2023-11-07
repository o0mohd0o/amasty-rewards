<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Frontend\Catalog;

use Amasty\Rewards\Api\GuestHighlightManagementInterface;

class HighlightCategory extends HighlightProduct
{
    public const CUSTOMER_COMPONENT = 'Amasty_Rewards/js/model/catalog/highlight-category';
    public const GUEST_COMPONENT = 'Amasty_Rewards/js/model/catalog/guest-highlight-category';

    /**
     * API path
     *
     * @var string
     */
    protected $path = 'rewards/mine/highlight/category';

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Amasty\Rewards\Api\GuestHighlightManagementInterface $guestHighlightManagement,
        \Amasty\Rewards\Model\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $sessionFactory, $guestHighlightManagement, $data);
        $this->config = $config;
    }

    /**
     * @return false|string
     */
    public function getJsLayout()
    {
        //Reset current data to prevent reloading components for processed products
        $this->jsLayout['components'] = null;

        $result = [
            'productId' => $this->getProductId(),
            'formSelector' => '[data-product-sku="' . $this->getProductSku() . '"]'
        ];

        if ($this->isLoggedIn()) {
            $result['refreshUrl'] = $this->getRefreshUrl();
            $result['component'] = self::CUSTOMER_COMPONENT;
        } else {
            $this->path = 'rewards/mine/guest-highlight/product';
            $result['refreshUrl'] = $this->getRefreshUrl();
            $result['component'] = self::GUEST_COMPONENT;
        }

        $this->jsLayout['components']['amasty-rewards-highlight-catalog-' . $this->getProductId()] = $result;

        return json_encode($this->jsLayout);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isVisible()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    protected function isVisible()
    {
        return $this->isVisibleForLoggedCustomers() || $this->isVisibleForGuestCustomers();
    }

    private function isVisibleForLoggedCustomers(): bool
    {
        return $this->isLoggedIn() && $this->config->getHighlightCategoryVisibility($this->_storeManager->getStore());
    }

    private function isVisibleForGuestCustomers(): bool
    {
        return !$this->isLoggedIn()
            && $this->guestHighlightManagement->isVisible(GuestHighlightManagementInterface::PAGE_CATEGORY);
    }

    /**
     * @param int $productId
     *
     * @return HighlightCategory
     */
    public function setProductId($productId)
    {
        return $this->setData('product_id', $productId);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->_getData('product_id');
    }

    /**
     * @param string $sku
     *
     * @return HighlightCategory
     */
    public function setProductSku($sku)
    {
        return $this->setData('product_sku', $sku);
    }

    /**
     * @return string
     */
    public function getProductSku()
    {
        return $this->_getData('product_sku');
    }
}
