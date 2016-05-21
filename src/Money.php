<?php namespace Votemike\Money;

use DomainException;
use InvalidArgumentException;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Intl\NumberFormatter\NumberFormatter;

class Money
{
    /**
     * @var float
     */
    private $amount;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var NumberFormatter
     */
    private $formatter;

    /**
     * @param $amount
     * @param $currency
     */
    public function __construct($amount, $currency)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Money only accepts numeric amounts');
        }

        if (!array_key_exists($currency, Intl::getCurrencyBundle()->getCurrencyNames())) {
            throw new InvalidArgumentException($currency . ' is not a supported currency');
        }
        $this->amount = (float)$amount;
        $this->currency = $currency;
        $this->formatter = new NumberFormatter('en', NumberFormatter::CURRENCY);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->format();
    }

    /**
     * Returns a positive clone of Money object
     *
     * @return static
     */
    public function abs()
    {
        return new static(abs($this->amount), $this->currency);
    }

    /**
     * @param Money $money
     * @return static
     */
    public function add(Money $money)
    {
        $this->assertCurrencyMatches($money);

        return new static($this->amount + $money->getAmount(), $this->currency);
    }

    /**
     * @param float $operator
     * @return static
     */
    public function divide($operator)
    {
        if ($operator == 0) {
            throw new InvalidArgumentException('Cannot divide by zero');
        }
        return new static($this->amount / $operator, $this->currency);
    }

    /**
     * Returns a rounded string with the currency symbol
     *
     * @param bool $displayCountryForUS Set to true if you would like 'US$' instead of just '$'
     * @return string
     */
    public function format($displayCountryForUS = false)
    {
        if ($displayCountryForUS && $this->currency === 'USD') {
            if ($this->amount >= 0) {
                return 'US' . $this->formatter->formatCurrency($this->amount, $this->currency);
            }
            return '-US' . $this->formatter->formatCurrency(-$this->amount, $this->currency);
        }
        return $this->formatter->formatCurrency($this->amount, $this->currency);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Returns the amount rounded to the correct number of decimal places for that currency
     *
     * @todo Change to use NumberFormatter's roundCurrency when https://github.com/symfony/symfony/pull/18827 is accepted
     *
     * @return float
     */
    public function getRoundedAmount()
    {
        //return (float)$this->formatter->roundCurrency($this->amount, $this->currency);
        return (float)$this->roundCurrency($this->amount, $this->currency);
    }

    /**
     * Invert the amount
     *
     * @return static
     */
    public function inv()
    {
        return new static(-$this->amount, $this->currency);
    }

    /**
     * @param $operator
     * @return static
     */
    public function multiply($operator)
    {
        return new static($this->amount * $operator, $this->currency);
    }

    /**
     * @param $percentage
     * @return static
     */
    public function percentage($percentage)
    {
        return new static(($this->amount * $percentage) / 100, $this->currency);
    }

    /**
     * Returns rounded clone of Money object, rounded to the correct number of decimal places for that currency
     *
     * @return static
     */
    public function round()
    {
        return new static($this->getRoundedAmount(), $this->currency);
    }

    /**
     * Pass in an array of percentages to allocate Money in those amounts.
     * Final entry in array gets any remaining units.
     * If the percentages total less than 100, remaining money is allocated to an extra return value.
     *
     * By default, the amounts are rounded to the correct number of decimal places for that currency. This can be disabled by passing false as the second argument.
     *
     * @param float[] $percentages An array of percentages that must total 100 or less
     * @param bool $round
     * @return array
     */
    public function split(array $percentages, $round = true)
    {
        $totalPercentage = array_sum($percentages);
        if ($totalPercentage > 100) {
            throw new InvalidArgumentException('Only 100% can be allocated');
        }
        $amounts = [];
        $total = 0;
        if (!$round) {
            foreach ($percentages as $percentage) {
                $share = $this->percentage($percentage);
                $total += $share->getAmount();
                $amounts[] = $share;
            }
            if ($totalPercentage != 100) {
                $amounts[] = new static($this->amount - $total, $this->currency);
            }
            return $amounts;
        }

        $count = 0;

        if ($totalPercentage != 100) {
            $percentages[] = 0; //Dummy record to trigger the rest of the amount being assigned to a final pot
        }

        foreach ($percentages as $percentage) {
            ++$count;
            if ($count == count($percentages)) {
                $amounts[] = new static($this->amount - $total, $this->currency);
            } else {
                $share = $this->percentage($percentage)->round();
                $total += $share->getAmount();
                $amounts[] = $share;
            }
        }

        return $amounts;
    }

    /**
     * @param Money $money
     * @return static
     */
    public function sub(Money $money)
    {
        $this->assertCurrencyMatches($money);

        return new static($this->amount - $money->getAmount(), $this->currency);
    }

    /**
     * @param Money $money
     */
    private function assertCurrencyMatches(Money $money)
    {
        if ($this->currency !== $money->getCurrency()) {
            throw new DomainException('Currencies must match');
        }
    }

    /**
     * Almost a copy of NumberFormatter roundCurrency
     *
     * @todo Remove once https://github.com/symfony/symfony/pull/18827 is accepted
     *
     * @param $value
     * @param $currency
     * @return float|Money
     */
    private function roundCurrency($value, $currency)
    {
        $fractionDigits = Intl::getCurrencyBundle()->getFractionDigits($currency);
        $roundingIncrement = Intl::getCurrencyBundle()->getRoundingIncrement($currency);

        // Round with the formatter rounding mode
        $value = round($value, $fractionDigits);

        // Swiss rounding
        if (0 < $roundingIncrement && 0 < $fractionDigits) {
            $roundingFactor = $roundingIncrement / pow(10, $fractionDigits);
            $value = round($value / $roundingFactor) * $roundingFactor;
        }

        return $value;
    }
}