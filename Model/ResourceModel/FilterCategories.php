<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class FilterCategories implements FilterExistingEntityInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param int[] $ids
     * @return array
     */
    public function execute(array $ids): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            $this->resourceConnection->getTableName('catalog_category_entity'),
            'entity_id'
        )->where(
            'entity_id IN (?)',
            $ids
        );

        return $connection->fetchCol($select);
    }
}
