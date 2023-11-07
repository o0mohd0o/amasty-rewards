<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Block\Product;

class ListProductPlugin
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product = null;

    public function beforeGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->product = $product;

        return [$product];
    }

    public function afterGetProductPrice(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
    {
        /** @var \Amasty\Rewards\Block\Frontend\Catalog\HighlightCategory $block */
        $block = $subject->getChildBlock('amasty_rewards_highlight');

        if (!$block) {
            return $result;
        }

        $block->setProductId($this->product->getId())->setProductSku($this->product->getSku());
        $result .= $block->toHtml();

        return $result;
    }
}
