<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Test\Unit\Model\Order;

use Amasty\Rewards\Api\Data\RewardsInterface;
use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Model\Order\CancelOrderProcessor;
use Amasty\Rewards\Model\Repository\RewardsRepository;
use Amasty\Rewards\Model\Config\Source\Actions;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see CancelOrderProcessor
 */
class CancelOrderProcessorTest extends TestCase
{
    private const ORDER_INCREMENT_ID = '00001';

    private const REWARDS_AMOUNT = 10.00;

    private const CUSTOMER_ID = 1;

    /**
     * @var RewardsProviderInterface|MockObject
     */
    private $rewardsProviderMock;

    /**
     * @var CancelOrderProcessor
     */
    private $subject;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var RewardsInterface|MockObject
     */
    private $modelRewardsMock;

    protected function setUp(): void
    {
        $this->rewardsProviderMock = $this->createMock(RewardsProviderInterface::class);
        $this->modelRewardsMock = $this->createMock(RewardsInterface::class);
        $rewardsRepositoryMock = $this->createConfiguredMock(
            RewardsRepository::class,
            ['getEmptyModel' => $this->modelRewardsMock]
        );
        $this->orderMock = $this->createConfiguredMock(
            Order::class,
            [
                'getIncrementId' => self::ORDER_INCREMENT_ID,
                'getCustomerId' => self::CUSTOMER_ID
            ]
        );

        $this->subject = new CancelOrderProcessor(
            $this->rewardsProviderMock,
            $rewardsRepositoryMock
        );
    }

    /**
     * @covers CancelOrderProcessor::refundUsedRewards
     */
    public function testRefundUsedRewards(): void
    {
        $this->modelRewardsMock->expects($this->once())->method('setCustomerId')->with(self::CUSTOMER_ID);
        $this->modelRewardsMock->expects($this->once())->method('setAmount')->with(self::REWARDS_AMOUNT);
        $this->modelRewardsMock->expects($this->once())->method('setComment')->with(
            'Order #' . self::ORDER_INCREMENT_ID . ' Canceled'
        );
        $this->modelRewardsMock->expects($this->once())->method('setAction')->with(Actions::CANCEL_ACTION);

        $this->rewardsProviderMock->expects($this->once())->method('addRewardPoints')->with(
            $this->modelRewardsMock
        );
        $this->subject->refundUsedRewards($this->orderMock, self::REWARDS_AMOUNT);
    }
}
