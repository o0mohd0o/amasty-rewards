<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Amasty\Rewards\Model\Sample
     */
    protected $sample;

    /**
     * Installer constructor.
     *
     * @param \Amasty\Rewards\Model\Sample $sample
     */
    public function __construct(\Amasty\Rewards\Model\Sample $sample)
    {
        $this->sample = $sample;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->sample->install(['Amasty_Rewards::fixtures/reward_rules.csv']);
    }
}
