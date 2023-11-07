<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Reward Points Base for Magento 2
 */

namespace Amasty\Rewards\Test\Unit\Model\Calculation\Discount;

use Amasty\Rewards\Model\Calculation\Discount\ItemAmountCalculator;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Tax\Model\Config as TaxConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see ItemAmountCalculator
 */
class ItemAmountCalculatorTest extends TestCase
{
    /**
     * @var TaxConfig|MockObject
     */
    private $taxConfigMock;

    /**
     * @var ItemAmountCalculator
     */
    private $subject;

    /**
     * @var QuoteItem|MockObject
     */
    private $quoteItemMock;

    /**
     * @var OrderItem|MockObject
     */
    private $orderItemMock;

    protected function setUp(): void
    {
        $this->taxConfigMock = $this->createMock(TaxConfig::class);
        $this->quoteItemMock = $this->createMock(QuoteItem::class);
        $this->orderItemMock = $this->createMock(OrderItem::class);

        $this->subject = new ItemAmountCalculator(
            $this->taxConfigMock
        );
    }

    /**
     * @param float $priceInclTax
     * @param float $discountAmount
     * @param int $qty
     * @param float $result
     *
     * @dataProvider calculateItemAmountProviderWithTax
     *
     * @covers ItemAmountCalculator::calculateItemAmount
     */
    public function testCalculateItemAmountWithOrderItemAndTax(
        float $priceInclTax,
        float $discountAmount,
        int $qty,
        float $result
    ): void {
        $this->taxConfigMock->expects($this->once())->method('discountTax')->willReturn(true);
        $this->orderItemMock->expects($this->once())->method('getBasePriceInclTax')->willReturn($priceInclTax);
        $this->orderItemMock->expects($this->once())->method('getBaseDiscountAmount')->willReturn($discountAmount);
        $this->orderItemMock->expects($this->once())->method('__call')->willReturn($qty);

        $this->assertEquals($result, $this->subject->calculateItemAmount($this->orderItemMock));
    }

    /**
     * @param float $priceInclTax
     * @param float $price
     * @param float $discountAmount
     * @param int $qty
     * @param float $result
     *
     * @dataProvider calculateItemAmountProviderWithoutTax
     *
     * @covers ItemAmountCalculator::calculateItemAmount
     */
    public function testCalculateItemAmountWithOrderItemAndWithoutTax(
        float $priceInclTax,
        float $price,
        float $discountAmount,
        int $qty,
        float $result
    ): void {
        $this->taxConfigMock->expects($this->once())->method('discountTax')->willReturn(false);
        $this->orderItemMock->expects($this->once())->method('getBasePriceInclTax')->willReturn($priceInclTax);
        $this->orderItemMock->expects($this->once())->method('getBasePrice')->willReturn($price);
        $this->orderItemMock->expects($this->once())->method('getBaseDiscountAmount')->willReturn($discountAmount);
        $this->orderItemMock->expects($this->once())->method('__call')->willReturn($qty);

        $this->assertEquals($result, $this->subject->calculateItemAmount($this->orderItemMock));
    }

    /**
     * @param float $priceInclTax
     * @param float $discountAmount
     * @param int $qty
     * @param float $result
     *
     * @dataProvider calculateItemAmountProviderWithTax
     *
     * @covers ItemAmountCalculator::calculateItemAmount
     */
    public function testCalculateItemAmountWithQuoteItemAndTax(
        float $priceInclTax,
        float $discountAmount,
        int $qty,
        float $result
    ): void {
        $this->taxConfigMock->expects($this->once())->method('discountTax')->willReturn(true);
        $this->quoteItemMock->expects($this->once())->method('getQty')->willReturn($qty);

        $this->quoteItemMock->expects($this->exactly(2))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getBasePriceInclTax', [], $priceInclTax],
                    ['getBaseDiscountAmount', [], $discountAmount],
                ]
            );

        $this->assertEquals($result, $this->subject->calculateItemAmount($this->quoteItemMock));
    }

    /**
     * @param float $priceInclTax
     * @param float $price
     * @param float $discountAmount
     * @param int $qty
     * @param float $result
     *
     * @dataProvider calculateItemAmountProviderWithoutTax
     *
     * @covers ItemAmountCalculator::calculateItemAmount
     */
    public function testCalculateItemAmountWithQuoteItemAndWithoutTax(
        float $priceInclTax,
        float $price,
        float $discountAmount,
        int $qty,
        float $result
    ): void {
        $this->taxConfigMock->expects($this->once())->method('discountTax')->willReturn(false);
        $this->quoteItemMock->expects($this->once())->method('getQty')->willReturn($qty);

        $this->quoteItemMock->expects($this->exactly(3))
            ->method('__call')
            ->willReturnMap(
                [
                    ['getBasePriceInclTax', [], $priceInclTax],
                    ['getBasePrice', [], $price],
                    ['getBaseDiscountAmount', [], $discountAmount],
                ]
            );

        $this->assertEquals($result, $this->subject->calculateItemAmount($this->quoteItemMock));
    }

    /**
     * @return array
     */
    public function calculateItemAmountProviderWithTax(): array
    {
        return [
            [11.00, 1.00, 1, 10.00],
            [11.00, 11.00, 1, 0.00],
            [11.00, 1.00, 2, 21.00],
            [11.00, 22.00, 2, 0.00],
        ];
    }

    /**
     * @return array
     */
    public function calculateItemAmountProviderWithoutTax(): array
    {
        return [
            [11.00, 10.00, 1.00, 1, 9.00],
            [11.00, 10.00, 11.00, 1, 0.00],
            [11.00, 10.00, 1.00, 2, 19.00],
            [11.00, 10.00, 22.00, 2, 0.00]
        ];
    }
}
