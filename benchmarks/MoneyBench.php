<?php

use Votemike\Money\Money;

class MoneyBench
{

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchAbs()
    {
        $money = new Money(10, 'USD');
        $money->abs();
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchAdd()
    {
        $money = new Money(10, 'USD');
        $money->add(new Money(10, 'USD'));
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchDivide()
    {
        $money = new Money(10, 'USD');
        $money->divide(3);
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchFormat()
    {
        $money = new Money(10, 'USD');
        $money->format();
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchFormatForAccounting()
    {
        $money = new Money(10, 'USD');
        $money->formatForAccounting();
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchFormatShorthand()
    {
        $money = new Money(10, 'USD');
        $money->formatShorthand();
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchFormatWithSign()
    {
        $money = new Money(10, 'USD');
        $money->formatWithSign();
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchGetRoundedAmount()
    {
        $money = new Money(10.222222, 'USD');
        $money->getRoundedAmount();
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchInv()
    {
        $money = new Money(10, 'USD');
        $money->inv();
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchMultiply()
    {
        $money = new Money(10, 'USD');
        $money->multiply(3);
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchPercentage()
    {
        $money = new Money(10, 'USD');
        $money->percentage(33.3);
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchRound()
    {
        $money = new Money(10, 'USD');
        $money->round();
    }

    public function provideBools()
    {
        return [
            ['bool' => true],
            ['bool' => false],
        ];
    }

    /**
     * @Iterations(5)
     * @ParamProviders({"provideBools"})
     * @Revs(500)
     */
    public function benchSplit($params)
    {
        $money = new Money(10, 'USD');
        $money->split([100 / 3, 25], $params['bool']);
    }

    /**
     * @Iterations(5)
     * @Revs(500)
     */
    public function benchSub()
    {
        $money = new Money(10, 'USD');
        $money->sub(new Money(10, 'USD'));
    }
}
