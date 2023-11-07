<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel;

interface FilterExistingEntityInterface
{
    /**
     * Filter given array of identifiers and return only existing.
     *
     * @param array $ids
     * @return array
     */
    public function execute(array $ids): array;
}
