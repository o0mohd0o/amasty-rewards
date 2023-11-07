<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Amasty\Rewards\Api\Data\ExpirationArgumentsInterface;

class ExpirationArguments implements ExpirationArgumentsInterface
{
    /**
     * @var bool
     */
    private $isExpire = false;

    /**
     * @var int
     */
    private $days = 0;

    /**
     * @inheritDoc
     */
    public function isExpire()
    {
        return $this->isExpire;
    }

    /**
     * @inheritDoc
     */
    public function setIsExpire($isExpire)
    {
        $this->isExpire = $isExpire;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @inheritDoc
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }
}
