<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Cart;

use Amasty\Rewards\Api\CheckoutHighlightManagementInterface;
use Magento\Quote\Api\Data\TotalsExtensionFactory;

class CartTotalRepositoryPlugin
{
    public const REGISTRY_IGNORE_EXTENSION_ATTRIBUTES_KEY = 'amasty_rewards_ignore_extension_attributes';

    /**
     * @var CheckoutHighlightManagementInterface
     */
    private $highlightManagement;

    /**
     * @var TotalsExtensionFactory
     */
    private $extensionFactory;

    public function __construct(
        CheckoutHighlightManagementInterface $highlightManagement,
        TotalsExtensionFactory $extensionFactory
    ) {
        $this->highlightManagement = $highlightManagement;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param \Magento\Quote\Model\Cart\CartTotalRepository $subject
     * @param \Magento\Quote\Model\Cart\Totals $result
     *
     * @return \Magento\Quote\Model\Cart\Totals
     */
    public function afterGet(\Magento\Quote\Model\Cart\CartTotalRepository $subject, $result)
    {
        $extensionAttributes = $result->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->extensionFactory->create();
        }

        $extensionAttributes->setAmastyRewardsHighlight(
            $this->highlightManagement->fillData()
        );

        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}
