<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Model;

use Magento\Framework\Model\AbstractModel;
use Amasty\Rewards\Api\Data\RewardsInterface;

class Rewards extends AbstractModel implements RewardsInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Rewards::class);
    }

    /**
     * @return string|null
     */
    public function getActionDate(): string
    {
        return $this->_getData(RewardsInterface::ACTION_DATE);
    }

    /**
     * @param string $actionDate
     * @return $this|RewardsInterface
     */
    public function setActionDate(string $actionDate): RewardsInterface
    {
        $this->setData(RewardsInterface::ACTION_DATE, $actionDate);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getAmount(): float
    {
        return (float)$this->_getData(RewardsInterface::AMOUNT);
    }

    /**
     * @param float $amount
     * @return $this|RewardsInterface
     */
    public function setAmount(float $amount): RewardsInterface
    {
        $this->setData(RewardsInterface::AMOUNT, $amount);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->_getData(RewardsInterface::COMMENT);
    }

    /**
     * @param string $comment
     * @return $this|RewardsInterface
     */
    public function setComment(string $comment): RewardsInterface
    {
        $this->setData(RewardsInterface::COMMENT, $comment);

        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return (string)$this->_getData(RewardsInterface::ACTION);
    }

    /**
     * @param string $action
     * @return $this|RewardsInterface
     */
    public function setAction(string $action): RewardsInterface
    {
        $this->setData(RewardsInterface::ACTION, $action);

        return $this;
    }

    /**
     * @return float|null
     */
    public function getPointsLeft(): ?float
    {
        return (float)$this->_getData(RewardsInterface::POINTS_LEFT);
    }

    /**
     * @param float $pointsLeft
     * @return $this|RewardsInterface
     */
    public function setPointsLeft(float $pointsLeft): RewardsInterface
    {
        $this->setData(RewardsInterface::POINTS_LEFT, $pointsLeft);

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int)$this->_getData(RewardsInterface::CUSTOMER_ID);
    }

    /**
     * @param int $customerId
     * @return RewardsInterface
     */
    public function setCustomerId(int $customerId): RewardsInterface
    {
        $this->setData(RewardsInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @return bool
     */
    public function getVisibleForCustomer(): bool
    {
        return (bool)$this->getData(RewardsInterface::VISIBLE_FOR_CUSTOMER);
    }

    /**
     * @param bool $visibleForCustomer
     * @return RewardsInterface
     */
    public function setVisibleForCustomer(bool $visibleForCustomer): RewardsInterface
    {
        $this->setData(RewardsInterface::VISIBLE_FOR_CUSTOMER, $visibleForCustomer);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdminName(): ?string
    {
        return $this->_getData(RewardsInterface::ADMIN_NAME);
    }

    /**
     * @param string $adminName
     * @return RewardsInterface
     */
    public function setAdminName(string $adminName): RewardsInterface
    {
        $this->setData(RewardsInterface::ADMIN_NAME, $adminName);

        return $this;
    }

    /**
     * @return string
     */
    public function getExpirationDate(): string
    {
        return $this->_getData(RewardsInterface::ACTION_DATE);
    }

    /**
     * @param string|null $date
     * @return $this|RewardsInterface
     */
    public function setExpirationDate(?string $date): RewardsInterface
    {
        $this->setData(RewardsInterface::EXPIRATION_DATE, $date);

        return $this;
    }

    /**
     * @return float
     */
    public function getExpiringAmount(): float
    {
        return (float)$this->_getData(RewardsInterface::EXPIRING_AMOUNT);
    }

    /**
     * @param float $amount
     * @return $this|RewardsInterface
     */
    public function setExpiringAmount(float $amount): RewardsInterface
    {
        $this->setData(RewardsInterface::EXPIRING_AMOUNT, $amount);

        return $this;
    }
}
