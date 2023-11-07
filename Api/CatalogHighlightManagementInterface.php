<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api;

interface CatalogHighlightManagementInterface
{
    /**
     * For product page only.
     *
     * @param int $productId
     * @param int $customerId
     * @param string|null $attributes
     *
     * @return \Amasty\Rewards\Api\Data\HighlightInterface
     */
    public function getHighlightForProduct($productId, $customerId, $attributes = null);

    /**
     * For category page only.
     *
     * @param int $productId
     * @param int $customerId
     * @param string|null $attributes
     *
     * @return \Amasty\Rewards\Api\Data\HighlightInterface
     */
    public function getHighlightForCategory($productId, $customerId, $attributes = null);

    /**
     * @param int $productId
     * @param string $attributes
     * @return float
     */
    public function getAmountForProductForGuest(int $productId, string $attributes = ''): float;
}
