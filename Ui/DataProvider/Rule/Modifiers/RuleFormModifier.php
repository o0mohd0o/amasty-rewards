<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Ui\DataProvider\Rule\Modifiers;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class RuleFormModifier implements ModifierInterface
{
    public const CATEGORIES_FIELD_NAME = 'categories';

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        foreach ($data as &$dataItem) {
            if (!empty($dataItem[self::CATEGORIES_FIELD_NAME])) {
                $dataItem[self::CATEGORIES_FIELD_NAME] = explode(',', $dataItem[self::CATEGORIES_FIELD_NAME]);
            }
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
