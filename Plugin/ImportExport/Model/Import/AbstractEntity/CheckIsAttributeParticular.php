<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\ImportExport\Model\Import\AbstractEntity;

use Magento\ImportExport\Model\Import\AbstractEntity as ImportAbstractEntity;
use Amasty\Rewards\Model\ConstantRegistryInterface;

class CheckIsAttributeParticular
{
    private const SPECIAL_ATTRIBUTES = [
        ConstantRegistryInterface::NOTIFICATION_EARNING,
        ConstantRegistryInterface::NOTIFICATION_EXPIRE,
        ConstantRegistryInterface::FORBID_EARNING
    ];

    /**
     * @var string
     */
    private $attributeCode;

    public function beforeIsAttributeParticular(
        ImportAbstractEntity $subject,
        string $attributeCode
    ): void {
        $this->attributeCode = $attributeCode;
    }

    public function afterIsAttributeParticular(
        ImportAbstractEntity $subject,
        bool $result
    ): bool {
        if (in_array($this->attributeCode, self::SPECIAL_ATTRIBUTES)) {
            return true;
        }

        return $result;
    }
}
