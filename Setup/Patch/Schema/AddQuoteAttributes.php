<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup\Patch\Schema;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Quote\Setup\QuoteSetupFactory;

class AddQuoteAttributes implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup, QuoteSetupFactory $quoteSetupFactory)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $quoteSetup = $this->quoteSetupFactory->create(
            ['resourceName' => 'quote_setup', 'setup' => $this->moduleDataSetup]
        );

        $quoteSetup->addAttribute('quote', EntityInterface::POINTS_SPENT, [
            'type' => Table::TYPE_DECIMAL,
            'visible' => false,
            'nullable' => true
        ]);

        $quoteSetup->addAttribute('quote_item', EntityInterface::POINTS_SPENT, [
            'type' => Table::TYPE_DECIMAL,
            'visible' => false,
            'nullable' => true
        ]);
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
