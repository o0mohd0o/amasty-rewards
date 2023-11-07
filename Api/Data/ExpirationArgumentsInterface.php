<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api\Data;

interface ExpirationArgumentsInterface
{
    public const IS_EXPIRE = 'is_expire';
    public const DAYS = 'days';

    /**
     * @param bool $expire
     *
     * @return ExpirationArgumentsInterface
     */
    public function setIsExpire($expire);

    /**
     * @return bool
     */
    public function isExpire();

    /**
     * @param int $days
     *
     * @return ExpirationArgumentsInterface
     */
    public function setDays($days);

    /**
     * @return int
     */
    public function getDays();
}
