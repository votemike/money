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
     * @param float|int|string $amount
     * @param string $currency
     */
    public function __construct($amount, string $currency)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Money only accepts numeric amounts');
        }

        if (!array_key_exists($currency, Intl::getCurrencyBundle()->getCurrencyNames())) {
            throw new InvalidArgumentException($currency . ' is not a supported currency');
        }
        $this->amount = (float)$amount;
        $this->currency = $currency;
    }

    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * Returns a positive clone of Money object
     */
    public function abs(): Money
    {
        return new static(abs($this->amount), $this->currency);
    }

    public function add(Money $money): Money
    {
        $this->assertCurrencyMatches($money);

        return new static($this->amount + $money->getAmount(), $this->currency);
    }

    /**
     * @param float $operator
     * @return static
     */
    public function divide($operator): Money
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
    public function format(bool $displayCountryForUS = false): string
    {
        $formatter = new NumberFormatter('en', NumberFormatter::CURRENCY);

        if ($displayCountryForUS && $this->currency === 'USD') {
            if ($this->amount >= 0) {
                return 'US' . $formatter->formatCurrency($this->amount, $this->currency);
            }
            return '-US' . $formatter->formatCurrency(-$this->amount, $this->currency);
        }
        return $formatter->formatCurrency($this->amount, $this->currency);
    }

    /**
     * Returns a rounded number without currency
     * If the number is negative, the currency is within parentheses
     */
    public function formatForAccounting(): string
    {
        $amount = $this->getRoundedAmount();
        $negative = 0 > $amount;
        if ($negative) {
            $amount *= -1;
        }
        $amount = number_format($amount, Intl::getCurrencyBundle()->getFractionDigits($this->currency));
        return $negative ? '(' . $amount . ')' : $amount;
    }

    /**
     * Returns a string consisting of the currency symbol, a rounded int and a suffix
     * e.g. $33k instead of $3321.12
     */
    public function formatShorthand(): string
    {
        $amount = $this->amount;
        $negative = 0 > $amount;
        if ($negative) {
            $amount *= -1;
        }
        $units = ['', 'k', 'm', 'bn', 'tn'];
        $power = $amount > 0 ? floor(log(round($amount), 1000)) : 0;
        $ret = Intl::getCurrencyBundle()->getCurrencySymbol($this->currency, 'en').round($amount / pow(1000, $power), 0). $units[$power];
        return $negative ? '-'.$ret : $ret;
    }

    /**
     * The same as format() except that positive numbers always include the + sign
     *
     * @param bool $displayCountryForUS Set to true if you would like 'US$' instead of just '$'
     * @return string
     */
    public function formatWithSign(bool $displayCountryForUS = false): string
    {
        $string = $this->format($displayCountryForUS);

        if ($this->amount <= 0) {
            return $string;
        }

        return '+' . $string;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Returns the amount rounded to the correct number of decimal places for that currency
     */
    public function getRoundedAmount(): float
    {
        $fractionDigits = Intl::getCurrencyBundle()->getFractionDigits($this->currency);
        $roundingIncrement = Intl::getCurrencyBundle()->getRoundingIncrement($this->currency);

        $value = round($this->amount, $fractionDigits);

        // Swiss rounding
        if (0 < $roundingIncrement && 0 < $fractionDigits) {
            $roundingFactor = $roundingIncrement / pow(10, $fractionDigits);
            $value = round($value / $roundingFactor) * $roundingFactor;
        }

        return $value;
    }

    /**
     * Invert the amount
     */
    public function inv(): Money
    {
        return new static(-$this->amount, $this->currency);
    }

    /**
     * @param float $operator
     * @return static
     */
    public function multiply($operator): Money
    {
        return new static($this->amount * $operator, $this->currency);
    }

    /**
     * A number between 0 and 100
     *
     * @param float $percentage
     * @return static
     */
    public function percentage($percentage): Money
    {
        return new static(($this->amount * $percentage) / 100, $this->currency);
    }

    /**
     * Returns rounded clone of Money object, rounded to the correct number of decimal places for that currency
     */
    public function round(): Money
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
     * @return Money[]
     */
    public function split(array $percentages, bool $round = true): array
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
    public function sub(Money $money): Money
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
}
