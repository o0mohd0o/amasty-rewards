<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CustomerGroups implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $customerGroupCollectionFactory;

    public function __construct(CollectionFactory $customerGroupCollectionFactory)
    {
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->customerGroupCollectionFactory->create()->load()->toOptionArray();
    }
}
