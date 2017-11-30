<?php namespace Votemike\Money\Tests;

use DomainException;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use Votemike\Money\Money;

class MoneyTest extends PHPUnit_Framework_TestCase
{
    public function amountDataProvider(): array
    {
        return [
            [1000.00, 1000.00],
            ['500', 500],
            ['3.99999', 3.99999],
        ];
    }

    /**
     * @dataProvider amountDataProvider
     * @param mixed $amount
     * @param float $expected
     */
    public function testCanRetrieveAmount($amount, $expected)
    {
        $money = new Money($amount, 'GBP');
        $this->assertEquals($expected, $money->getAmount());
    }

    public function currencyCodeDataProvider(): array
    {
        return [
            ['USD'],
            ['GBP'],
            ['CHF'],
            ['JPY'],
        ];
    }

    /**
     * @dataProvider currencyCodeDataProvider
     * @param string $currencyCode
     */
    public function testCanRetrieveCurrency(string $currencyCode)
    {
        $money = new Money(1000, $currencyCode);
        $this->assertEquals($currencyCode, $money->getCurrency());
    }

    public function invalidAmountDataProvider()
    {
        return [
            ['Mike'],
            [true],
        ];
    }

    /**
     * @dataProvider invalidAmountDataProvider
     * @param mixed $amount
     */
    public function testCanOnlyConstructWithNumericValues($amount)
    {
        $this->expectException(InvalidArgumentException::class);
        new Money($amount, 'USD');
    }

    public function absAmountDataProvider()
    {
        return [
            ['-9.9999', 9.9999],
            [-10, 10],
            [10.00, 10.00],
        ];
    }

    /**
     * @dataProvider absAmountDataProvider
     * @param mixed $amount
     * @param float $expected
     */
    public function testAbsoluteMoney($amount, $expected)
    {
        $money = new Money($amount, 'USD');
        $this->assertEquals($expected, $money->abs()->getAmount());
    }

    public function invertedAmountDataProvider(): array
    {
        return [
            ['-9.9999', 9.9999],
            [-10, 10],
            [10.00, -10.00],
        ];
    }

    /**
     * @dataProvider invertedAmountDataProvider
     * @param mixed $amount
     * @param float $expected
     */
    public function testInvertedMoney($amount, $expected)
    {
        $money = new Money($amount, 'USD');
        $this->assertEquals($expected, $money->inv()->getAmount());
    }

    public function testAddingMoney()
    {
        $first = new Money(10, 'USD');
        $second = new Money(20, 'USD');
        $this->assertEquals(30, $first->add($second)->getAmount());

        $third = new Money(30, 'USD');
        $this->assertEquals(60, $first->add($second)->add($third)->getAmount());
    }

    public function testAddingDifferentCurrenciesThrowsException()
    {
        $first = new Money(10, 'USD');
        $second = new Money(20, 'GBP');
        $this->expectException(DomainException::class);
        $first->add($second);
    }

    public function testSubtractingMoney()
    {
        $first = new Money(10, 'USD');
        $second = new Money(20, 'USD');
        $this->assertEquals(-10, $first->sub($second)->getAmount());

        $third = new Money(30, 'USD');
        $this->assertEquals(-40, $first->sub($second)->sub($third)->getAmount());
    }

    public function testSubtractingDifferentCurrenciesThrowsException()
    {
        $first = new Money(10, 'USD');
        $second = new Money(20, 'GBP');
        $this->expectException(DomainException::class);
        $first->sub($second);
    }

    public function testDividing()
    {
        $money = new Money(10, 'USD');
        $this->assertEquals(2.50, $money->divide(4)->getAmount());

        $this->expectException(InvalidArgumentException::class);
        $money->divide(0);
    }

    public function testMultiplying()
    {
        $money = new Money(10, 'USD');
        $this->assertEquals(30, $money->multiply(3)->getAmount());
    }

    public function formattingDataProvider(): array
    {
        return [
            ['10.000', 'USD', '$10.00'],
            [10, 'USD', '$10.00'],
            [9.995000000, 'USD', '$10.00'],
            [-10, 'USD', '-$10.00'],
            [-10, 'CAD', '-CA$10.00'],
            [-10, 'BHD', '-BHD10.000'],
            ['9.99950000', 'BHD', 'BHD10.000'],
            [-10.4999, 'JPY', '-¥10'],
        ];
    }

    /**
     * @dataProvider formattingDataProvider
     * @param mixed $amount
     * @param string $currencyCode
     * @param string $expected
     */
    public function testFormatting($amount, string $currencyCode, $expected)
    {
        $money = new Money($amount, $currencyCode);
        $this->assertEquals($expected, (string)$money);
    }

    public function manualFormattingDataProvider(): array
    {
        return [
            [-10, 'USD', false, '-$10.00'],
            [-10, 'USD', true, '-US$10.00'],
            [-10, 'CAD', false, '-CA$10.00'],
            [-10, 'CAD', true, '-CA$10.00'],
        ];
    }

    /**
     * @dataProvider manualFormattingDataProvider
     * @param mixed $amount
     * @param string $currencyCode
     * @param bool $displayCountryForUS
     * @param string $expected
     */
    public function testManualFormatting($amount, string $currencyCode, bool $displayCountryForUS, string $expected)
    {
        $money = new Money($amount, $currencyCode);
        $this->assertEquals($expected, $money->format($displayCountryForUS));
    }

    public function forcedPlusFormattingDataProvider(): array
    {
        return [
            [0, 'USD', false, '$0.00'],
            [10, 'USD', false, '+$10.00'],
            [-10, 'USD', false, '-$10.00'],
            [0, 'USD', true, 'US$0.00'],
            [10, 'USD', true, '+US$10.00'],
            [-10, 'USD', true, '-US$10.00'],
        ];
    }

    /**
     * @dataProvider forcedPlusFormattingDataProvider
     * @param mixed $amount
     * @param string $currencyCode
     * @param bool $displayCountryForUS
     * @param string $expected
     */
    public function testForcePlusFormatting($amount, string $currencyCode, bool $displayCountryForUS, string $expected)
    {
        $money = new Money($amount, $currencyCode);
        $this->assertEquals($expected, $money->formatWithSign($displayCountryForUS));
    }

    public function accountingFormattingDataProvider(): array
    {
        return [
            [0, 'USD', '0.00'],
            [-0.001, 'USD', '0.00'],
            [12.345, 'GBP', '12.35'],
            [-987.654, 'CAD', '(987.65)'],
            [-1.2345, 'JPY', '(1)'],
        ];
    }

    /**
     * @dataProvider accountingFormattingDataProvider
     * @param int|float $amount
     * @param string $currencyCode
     * @param string $expected
     */
    public function testAccountingFormatting($amount, string $currencyCode, string $expected)
    {
        $money = new Money($amount, $currencyCode);
        $this->assertSame($expected, $money->formatForAccounting());
    }

    public function roundingDataProvider(): array
    {
        return [
            ['10.000', 'USD', 10.00],
            [10, 'USD', 10.00],
            [9.995000000, 'USD', 10.00],
            [-10, 'USD', -10.00],
            [-10, 'CAD', -10.00],
            [-10, 'BHD', -10.000],
            ['9.99950000', 'BHD', 10.000],
            [-10.4999, 'JPY', -10],
        ];
    }

    /**
     * @dataProvider roundingDataProvider
     * @param mixed $amount
     * @param string $currencyCode
     * @param string $expected
     */
    public function testRoundedAmount($amount, string $currencyCode, $expected)
    {
        $money = new Money($amount, $currencyCode);
        $this->assertEquals($expected, $money->getRoundedAmount());
        $this->assertEquals($expected, $money->round()->getAmount());
    }

    public function invalidCurrencyDataProvider(): array
    {
        return [
            ['ZZZ'],
            ['AAA'],
        ];
    }

    /**
     * @dataProvider invalidCurrencyDataProvider
     * @param string $currencyCode
     */
    public function testValidCurrency(string $currencyCode)
    {
        $this->expectException(InvalidArgumentException::class);
        new Money(10.000000, $currencyCode);
    }

    public function percentageDataProvider(): array
    {
        return [
            ['2.222222', 50, 1.111111],
            [2.222222, 100, 2.222222],
            [1, 20, 0.2],
        ];
    }

    /**
     * @dataProvider percentageDataProvider
     * @param mixed $amount
     * @param int $percentage
     * @param float $expected
     */
    public function testGettingPercentage($amount, int $percentage, float $expected)
    {
        $money = new Money($amount, 'USD');
        $this->assertEquals($expected, $money->percentage($percentage)->getAmount());
    }

    public function testSplitPercentages()
    {
        $money = new Money(1200, 'USD');
        list($first, $second, $third) = $money->split([60, 30, 10]);
        $this->assertEquals(720, $first->getAmount());
        $this->assertEquals(360, $second->getAmount());
        $this->assertEquals(120, $third->getAmount());

        $money = new Money(1200, 'USD');
        $aThird = 100 / 3;
        $twoThirds = 200 / 3;
        list($first, $second) = $money->split([$twoThirds, $aThird]);
        $this->assertEquals(800, $first->getAmount());
        $this->assertEquals(400, $second->getAmount());

        $money = new Money(100, 'USD');
        list($first, $second) = $money->split([$twoThirds, $aThird]);
        $this->assertEquals(66.67, $first->getAmount());
        $this->assertEquals(33.33, $second->getAmount());

        list($first, $second, $third) = $money->split([$aThird, $aThird, $aThird]);
        $this->assertEquals(33.33, $first->getAmount());
        $this->assertEquals(33.33, $second->getAmount());
        $this->assertEquals(33.34, $third->getAmount());

        $money = new Money(100, 'USD');
        list($first, $second) = $money->split([$twoThirds, $aThird], false);
        $this->assertEquals(66.666666666666666, $first->getAmount());
        $this->assertEquals(33.333333333333333, $second->getAmount());

        $money = new Money(100, 'JPY');
        list($first, $second) = $money->split([$twoThirds, $aThird]);
        $this->assertEquals(67, $first->getAmount());
        $this->assertEquals(33, $second->getAmount());

        list($first, $second, $third) = $money->split([$aThird, $aThird]);
        $this->assertEquals(33, $first->getAmount());
        $this->assertEquals(33, $second->getAmount());
        $this->assertEquals(34, $third->getAmount());

        $money = new Money(100, 'USD');
        list($first, $second, $third) = $money->split([$aThird, $aThird]);
        $this->assertEquals(33.33, $first->getAmount());
        $this->assertEquals(33.33, $second->getAmount());
        $this->assertEquals(33.34, $third->getAmount());

        list($first, $second, $third) = $money->split([$aThird, $aThird], false);
        $this->assertEquals(33.333333333333333, $first->getAmount());
        $this->assertEquals(33.333333333333333, $second->getAmount());
        $this->assertEquals(33.333333333333334, $third->getAmount());

        $this->expectException(InvalidArgumentException::class);
        $money->split([60, 30, 20]);
    }

    public function testShorthandFormatting()
    {
        $money = new Money(0.0000, 'GBP');
        $this->assertEquals('£0', $money->formatShorthand());

        $money = new Money(33.333, 'USD');
        $this->assertEquals('$33', $money->formatShorthand());
        $this->assertEquals('-$33', $money->inv()->formatShorthand());

        $money = new Money(999.5, 'JPY');
        $this->assertEquals('¥1k', $money->formatShorthand());
        $this->assertEquals('-¥1k', $money->inv()->formatShorthand());

        $money = new Money(9500, 'CAD');
        $this->assertEquals('CA$10k', $money->formatShorthand());
        $this->assertEquals('-CA$10k', $money->inv()->formatShorthand());

        $money = new Money(77777.777, 'GBP');
        $this->assertEquals('£78k', $money->formatShorthand());
        $this->assertEquals('-£78k', $money->inv()->formatShorthand());

        $money = new Money(111111.111, 'USD');
        $this->assertEquals('$111k', $money->formatShorthand());
        $this->assertEquals('-$111k', $money->inv()->formatShorthand());

        $money = new Money(999999.5, 'JPY');
        $this->assertEquals('¥1m', $money->formatShorthand());
        $this->assertEquals('-¥1m', $money->inv()->formatShorthand());

        $money = new Money(3333333.33333, 'GBP');
        $this->assertEquals('£3m', $money->formatShorthand());
        $this->assertEquals('-£3m', $money->inv()->formatShorthand());

        $money = new Money(77777777.333, 'USD');
        $this->assertEquals('$78m', $money->formatShorthand());
        $this->assertEquals('-$78m', $money->inv()->formatShorthand());

        $money = new Money(77777777777.333, 'USD');
        $this->assertEquals('$78bn', $money->formatShorthand());
        $this->assertEquals('-$78bn', $money->inv()->formatShorthand());

        $money = new Money(333377777777777.333, 'USD');
        $this->assertEquals('$333tn', $money->formatShorthand());
        $this->assertEquals('-$333tn', $money->inv()->formatShorthand());
    }
}
