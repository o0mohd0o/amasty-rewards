<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup;

use Amasty\Base\Setup\SerializedFieldDataConverter;
use Amasty\Rewards\Model\ResourceModel\Rewards;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Recurring Post-Updates Data script
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var SerializedFieldDataConverter
     */
    private $fieldDataConverter;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        ProductMetadataInterface $productMetadata,
        SerializedFieldDataConverter $fieldDataConverter
    ) {
        $this->productMetadata = $productMetadata;
        $this->fieldDataConverter = $fieldDataConverter;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2', '>=')) {
            $this->convertSerializedDataToJson($setup);
        }
    }
    /**
     * Convert metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     */
    public function convertSerializedDataToJson($setup)
    {
        $this->fieldDataConverter->convertSerializedDataToJson(
            $setup->getTable('amasty_rewards_rule'),
            'rule_id',
            'conditions_serialized'
        );
    }
}
