<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Config\Backend;

class Round extends \Magento\Framework\App\Config\Value
{
    /**
     * @return \Magento\Framework\App\Config\Value|void
     */
    public function beforeSave()
    {
        $this->setValue((float)$this->getValue());

        parent::beforeSave();
    }
}
