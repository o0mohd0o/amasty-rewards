<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup\Patch\Data;

use Amasty\Rewards\Setup\Installer;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SampleData\Executor;

class AddSampleRules implements DataPatchInterface
{
    /**
     * @var Executor
     */
    private $executor;

    /**
     * @var Installer
     */
    private $installer;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    public function __construct(
        Executor $executor,
        Installer $installer,
        ResourceInterface $moduleResource
    ) {
        $this->executor = $executor;
        $this->installer = $installer;
        $this->moduleResource = $moduleResource;
    }

    public function apply(): void
    {
        $setupVersion = $this->moduleResource->getDbVersion('Amasty_Rewards');

        // Check if module was already installed or not.
        // If setup_version present in DB then we don't need to install fixtures, because setup_version is a marker.
        if (!$setupVersion) {
            $this->executor->exec($this->installer);
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
