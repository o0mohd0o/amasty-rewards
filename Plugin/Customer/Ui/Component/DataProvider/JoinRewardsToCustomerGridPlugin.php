<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Plugin\Customer\Ui\Component\DataProvider;

use Magento\Customer\Ui\Component\DataProvider;
use Magento\Framework\Api\Search\SearchResultInterface;

class JoinRewardsToCustomerGridPlugin
{
    public const ALIAS_FIELD_NAME = 'amount';
    public const ALIAS_TABLE_NAME = 'amrewards';
    public const TABLE_FIELD = 'points_left';
    public const TABLE_NAME = 'customer_grid_flat';
    public const REWARDS_TABLE = 'amasty_rewards_rewards';

    /**
     * @param DataProvider $subject
     * @param SearchResultInterface $collection
     *
     * @return SearchResultInterface
     */
    public function afterGetSearchResult(
        DataProvider $subject,
        SearchResultInterface $collection
    ): SearchResultInterface {
        if ($collection->getMainTable() === $collection->getTable(self::TABLE_NAME)) {
            $rewardsTableName = $collection->getTable(self::REWARDS_TABLE);
            $collection
                ->getSelect()
                ->joinLeft(
                    [self::ALIAS_TABLE_NAME => $rewardsTableName],
                    'main_table.entity_id = ' . self::ALIAS_TABLE_NAME . '.customer_id AND '
                    . self::ALIAS_TABLE_NAME . '.id IN (SELECT MAX(id) FROM '
                    . $rewardsTableName . ' GROUP BY customer_id)',
                    [self::ALIAS_FIELD_NAME => self::TABLE_FIELD]
                );
        }

        return $collection;
    }
}
