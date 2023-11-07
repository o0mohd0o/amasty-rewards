<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api;

use Amasty\Rewards\Api\Data\ExpirationArgumentsInterface;
use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Api\Data\RuleInterface;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface RewardsProviderInterface
 *
 * @api
 */
interface RewardsProviderInterface
{
    /**
     * Function to add rewards points to customer account by his ID according to Rewards Rule.
     * Argument $amount should be greater than zero or null.
     * If $amount/$comment is null, the value will be taken from the $rule.
     *
     * @param RuleInterface $rule
     * @param int $customerId
     * @param int $storeId
     * @param int|null $amount
     * @param string|null $comment
     *
     * @return void
     *
     * @throws LocalizedException
     * @throws InvalidArgumentException
     */
    public function addPointsByRule($rule, $customerId, $storeId, $amount = null, $comment = null);

    /**
     * @param RewardsInterface $modelRewards
     * @param int|null $storeId
     * @return void
     */
    public function addRewardPoints(RewardsInterface $modelRewards, ?int $storeId = null): void;

    /**
     * @param RewardsInterface $modelRewards
     * @return void
     */
    public function deductRewardPoints(RewardsInterface $modelRewards): void;
}
