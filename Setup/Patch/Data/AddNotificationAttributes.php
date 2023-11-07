<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup\Patch\Data;

use Amasty\Rewards\Model\ConstantRegistryInterface as Constant;
use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddNotificationAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup, EavSetupFactory $eavSetupFactory)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply(): void
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        if (!$eavSetup->getAttribute(Customer::ENTITY, Constant::NOTIFICATION_EARNING)) {
            $eavSetup->addAttribute(
                Customer::ENTITY,
                Constant::NOTIFICATION_EARNING,
                [
                    'type' => 'int',
                    'visible' => 0,
                    'required' => false,
                    'visible_on_front' => 1,
                    'is_user_defined' => 0,
                    'is_system' => 1,
                    'is_hidden' => 1,
                    'label' => ''
                ]
            );
        }

        if (!$eavSetup->getAttribute(Customer::ENTITY, Constant::NOTIFICATION_EXPIRE)) {
            $eavSetup->addAttribute(
                Customer::ENTITY,
                Constant::NOTIFICATION_EXPIRE,
                [
                    'type' => 'int',
                    'visible' => 0,
                    'required' => false,
                    'visible_on_front' => 1,
                    'is_user_defined' => 0,
                    'is_system' => 1,
                    'is_hidden' => 1,
                    'label' => ''
                ]
            );
        }
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
