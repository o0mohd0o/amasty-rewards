<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Quote;

use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\SuitableItemChecker;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class SpendingChecker
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var SuitableItemChecker
     */
    private $suitableItemChecker;

    public function __construct(Config $configProvider, SuitableItemChecker $suitableItemChecker)
    {
        $this->configProvider = $configProvider;
        $this->suitableItemChecker = $suitableItemChecker;
    }

    /**
     * @param AbstractItem $item
     * @param int|null $storeId
     * @return bool
     */
    public function isPossibleSpendOnItem(AbstractItem $item, int $storeId = null): bool
    {
        if ($item->getProductType() === Type::TYPE_BUNDLE
            && $item->getProduct()->getPriceType() == Price::PRICE_TYPE_DYNAMIC) {
            return false;
        }

        if ($item->getParentItem() && $item->getParentItem()->getProductType() === Configurable::TYPE_CODE) {
            return false;
        }

        if ($this->configProvider->isSpecificProductsEnabled($storeId)
            && (!empty($this->configProvider->getProductsSku($storeId))
                || !empty($this->configProvider->getProductsCategory($storeId))
            )
        ) {
            $skuIds = $this->configProvider->getProductsSku($storeId);
            $categoryIds = $this->configProvider->getProductsCategory($storeId);
            $action = $this->configProvider->getSpecificProductsAction($storeId);

            return $this->suitableItemChecker->isSuitableItem($item, $action, $skuIds, $categoryIds);
        }

        return true;
    }
}
