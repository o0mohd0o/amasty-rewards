<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Layout;

use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\Context;

class LayoutMoveDirectiveChange
{
    public const MAGENTO_BLANK_THEME_CODE = 'Magento/blank';

    public const CHECKOUT_CART_INDEX_ACTION_NAME = 'checkout_cart_index';

    public const AMREWARD_LAYOUT_BLOCK_NAME = 'checkout.cart.amreward';

    public const CART_CONTAINER_NAME = 'checkout.cart.container';

    public const CART_FORM_BLOCK_NAME = 'checkout.cart.form';

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    private $themeInfo;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\View\Design\ThemeInterface $themeInfo,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->appState = $appState;
        $this->themeInfo = $themeInfo;
        $this->themeProvider = $themeProvider;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * @param $context
     * @param Context $readerContext
     * @param Element $currentElement
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeInterpret($context, Context $readerContext, Element $currentElement)
    {
        if ($this->isAvailableToChangeDirective($currentElement)) {
            $currentElement->setAttribute('destination', self::CART_CONTAINER_NAME);
            $currentElement->setAttribute('after', self::CART_FORM_BLOCK_NAME);
        }

        return [$readerContext, $currentElement];
    }

    /**
     * @param Element $currentElement
     * @return bool
     */
    private function isAvailableToChangeDirective(Element $currentElement)
    {
        $imMoveableBlocks = self::AMREWARD_LAYOUT_BLOCK_NAME;
        $themeCode = (string)$this->getCurrentThemeCode();

        return $this->isFrontend()
            && $this->request->getFullActionName() === self::CHECKOUT_CART_INDEX_ACTION_NAME
            && $themeCode
            && $themeCode === self::MAGENTO_BLANK_THEME_CODE
            && ($currentElement->getAttribute('element') == $imMoveableBlocks);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isFrontend()
    {
        return $this->appState->getAreaCode() == \Magento\Framework\App\Area::AREA_FRONTEND;
    }

    /**
     * @return string
     */
    private function getCurrentThemeCode()
    {
        $themeId = $this->scopeConfig->getValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $this->themeProvider->getThemeById($themeId);

        return $theme->getId() ? $theme->getCode() : '';
    }
}
