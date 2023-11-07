<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api;

/**
 * @api
 */
interface GuestHighlightManagementInterface
{
    /**#@+
     * Values of $page argument.
     */
    public const PAGE_PRODUCT = 0;
    public const PAGE_CART = 1;
    public const PAGE_CHECKOUT = 2;
    public const PAGE_CATEGORY = 3;
    /**#@-*/

    /**
     * @param int $page
     * @param int|null $productId
     * @param string $attributes
     * @return \Amasty\Rewards\Api\Data\HighlightInterface|null
     */
    public function getHighlight($page, $productId = null, $attributes = '');

    /**
     * @return mixed
     */
    public function getRegistrationLink();

    /**
     * @param int $page
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function isVisible($page);
}
