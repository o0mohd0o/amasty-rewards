<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Block\Frontend\Catalog;

use Amasty\Rewards\Api\GuestHighlightManagementInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\View\Element\Template;

class HighlightProduct extends Template
{
    use \Amasty\Rewards\Model\ArrayPathTrait;

    public const API_METHOD = 'rest';
    public const API_VERSION = 'V1';
    public const GUEST_COMPONENT = 'Amasty_Rewards/js/model/catalog/guest-highlight-product';

    /**
     * API path
     *
     * @var string
     */
    protected $path = 'rewards/mine/highlight/product';

    /**
     * @var CustomerSessionFactory
     */
    private $sessionFactory;

    /**
     * @var GuestHighlightManagementInterface
     */
    protected $guestHighlightManagement;

    public function __construct(
        Template\Context $context,
        CustomerSessionFactory $sessionFactory,
        GuestHighlightManagementInterface $guestHighlightManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->sessionFactory = $sessionFactory;
        $this->guestHighlightManagement = $guestHighlightManagement;
    }

    public function getJsLayout()
    {
        $path = 'components/amasty-rewards-highlight-catalog';
        if ($this->isLoggedIn()) {
            $this->setToArrayByPath(
                $this->jsLayout,
                $path,
                [
                    'productId' => $this->getProductId(),
                    'refreshUrl' => $this->getRefreshUrl()
                ]
            );
        } elseif ($this->guestHighlightManagement->isVisible(GuestHighlightManagementInterface::PAGE_PRODUCT)) {
            $this->setToArrayByPath(
                $this->jsLayout,
                $path . '/component',
                self::GUEST_COMPONENT,
                false
            );
            $this->path = 'rewards/mine/guest-highlight/product';
            $this->setToArrayByPath(
                $this->jsLayout,
                $path,
                [
                    'productId' => $this->getProductId(),
                    'refreshUrl' => $this->getRefreshUrl()
                ]
            );
        } else {
            $this->unsetArrayValueByPath($this->jsLayout, $path);
        }

        return parent::getJsLayout();
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        if (!$this->hasData('product_id')) {
            if (!empty($this->_request->getParam('product_id'))) {
                $this->setData('product_id', $this->_request->getParam('product_id'));
            } else {
                $this->setData('product_id', $this->_request->getParam('id'));
            }
        }

        return $this->_getData('product_id');
    }

    /**
     * @return bool
     */
    protected function isLoggedIn()
    {
        return $this->sessionFactory->create()->isLoggedIn();
    }

    /**
     * @return string
     */
    protected function getRefreshUrl()
    {
        $data = [
            self::API_METHOD,
            $this->_storeManager->getStore()->getCode(),
            self::API_VERSION,
            $this->path
        ];

        return $this->getUrl(implode('/', $data));
    }
}
