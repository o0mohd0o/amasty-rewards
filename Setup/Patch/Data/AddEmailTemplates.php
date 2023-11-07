<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup\Patch\Data;

use Amasty\Rewards\Model\TemplateSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddEmailTemplates implements DataPatchInterface
{
    /**
     * @var TemplateSetup
     */
    private $templateSetup;

    public function __construct(TemplateSetup $templateSetup)
    {
        $this->templateSetup = $templateSetup;
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $this->templateSetup->createTemplate(
            'amrewards_notification_balance_earn_template_modern',
            'Amasty Rewards: Reward Points Earned Modern'
        );
        $this->templateSetup->createTemplate(
            'amrewards_notification_balance_earn_template',
            'Amasty Rewards: Reward Points Earned'
        );
        $this->templateSetup->createTemplate(
            'amrewards_notification_points_expiring_template',
            'Amasty Rewards: Reward Points Expiring'
        );
        $this->templateSetup->createTemplate(
            'amrewards_notification_points_expiring_template_modern',
            'Amasty Rewards: Reward Points Expiring Modern'
        );
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
