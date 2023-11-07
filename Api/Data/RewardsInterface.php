<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Api\Data;

interface RewardsInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const ACTION_DATE = 'action_date';
    public const AMOUNT = 'amount';
    public const COMMENT = 'comment';
    public const ACTION = 'action';
    public const POINTS_LEFT = 'points_left';
    public const CUSTOMER_ID = 'customer_id';
    public const VISIBLE_FOR_CUSTOMER = 'visible_for_customer';
    public const ADMIN_NAME = 'admin_name';
    public const EXPIRATION_DATE = 'expiration_date';
    public const EXPIRING_AMOUNT = 'expiring_amount';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * Identifier setter
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getActionDate(): string;

    /**
     * @param string $actionDate
     *
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     */
    public function setActionDate(string $actionDate): RewardsInterface;

    /**
     * @return float
     */
    public function getAmount(): float;

    /**
     * @param float $amount
     *
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     */
    public function setAmount(float $amount): RewardsInterface;

    /**
     * @return string|null
     */
    public function getComment(): ?string;

    /**
     * @param string $comment
     *
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     */
    public function setComment(string $comment): RewardsInterface;

    /**
     * @return string
     */
    public function getAction(): string;

    /**
     * @param string $action
     *
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     */
    public function setAction(string $action): RewardsInterface;

    /**
     * @return float
     */
    public function getPointsLeft(): ?float;

    /**
     * @param float $pointsLeft
     *
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     */
    public function setPointsLeft(float $pointsLeft): RewardsInterface;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     *
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     */
    public function setCustomerId(int $customerId): RewardsInterface;

    /**
     * @return bool
     */
    public function getVisibleForCustomer(): bool;

    /**
     * @param bool $visibleForCustomer
     * @return RewardsInterface
     */
    public function setVisibleForCustomer(bool $visibleForCustomer): RewardsInterface;

    /**
     * @return string|null
     */
    public function getAdminName(): ?string;

    /**
     * @param string $adminName
     * @return RewardsInterface
     */
    public function setAdminName(string $adminName): RewardsInterface;

    /**
     * @return string
     */
    public function getExpirationDate(): string;

    /**
     * @param string|null $date
     *
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     */
    public function setExpirationDate(?string $date): RewardsInterface;

    /**
     * @return float
     */
    public function getExpiringAmount(): float;

    /**
     * @param float $amount
     *
     * @return \Amasty\Rewards\Api\Data\RewardsInterface
     */
    public function setExpiringAmount(float $amount): RewardsInterface;
}
