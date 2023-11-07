<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Rule\Metadata;

/**
 * Metadata provider for rewards rule edit form.
 */
class ValueProvider
{
    /**
     * Get metadata for rewards rule form. It will be merged with form UI component declaration.
     *
     * @param \Amasty\Rewards\Model\Rule $rule
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getMetadataValues(\Amasty\Rewards\Model\Rule $rule)
    {
        $labels = $rule->getStoreLabels();

        return [
            'labels' => [
                'children' => [
                    'store_labels[0]' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'value' => isset($labels[0]) ? $labels[0] : '',
                                ],
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
