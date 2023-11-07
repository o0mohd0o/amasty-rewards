<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\ViewModel\System\Config;

use Amasty\Rewards\Model\Config\Source\IncludeExclude;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Note implements ArgumentInterface
{
    public const SKU_FIELD_PATH = 'amrewards_points_spending_config_sku';
    public const CATEGORY_FIELD_PATH = 'amrewards_points_spending_config_category';

    /**
     * @var array
     */
    private $noteMap = [];

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    public function __construct(JsonSerializer $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
        $this->_construct();
    }

    private function _construct(): void
    {
        $this->noteMap[IncludeExclude::INCLUDE_VALUE][self::SKU_FIELD_PATH]
            = __('Specify a comma-separated list of SKUs customers can spend the reward points on.');
        $this->noteMap[IncludeExclude::EXCLUDE_VALUE][self::SKU_FIELD_PATH]
            = __('Specify a comma-separated list of SKUs customers can’t spend the reward points on.');
        $this->noteMap[IncludeExclude::INCLUDE_VALUE][self::CATEGORY_FIELD_PATH]
            = __('Specify a comma-separated list of category IDs customers can spend the reward points on.');
        $this->noteMap[IncludeExclude::EXCLUDE_VALUE][self::CATEGORY_FIELD_PATH]
            = __('Specify a comma-separated list of category IDs customers can’t spend the reward points on.');
    }

    /**
     * @return string
     */
    public function getMap(): string
    {
        return $this->jsonSerializer->serialize($this->noteMap);
    }
}
