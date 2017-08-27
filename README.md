# Money

[![Build Status](https://travis-ci.org/votemike/money.svg?branch=master)](https://travis-ci.org/votemike/money)
[![Latest Stable Version](https://poser.pugx.org/votemike/money/v/stable)](https://packagist.org/packages/votemike/money)
[![Total Downloads](https://poser.pugx.org/votemike/money/downloads)](https://packagist.org/packages/votemike/money)
[![Latest Unstable Version](https://poser.pugx.org/votemike/money/v/unstable)](https://packagist.org/packages/votemike/money)
[![License](https://poser.pugx.org/votemike/money/license)](https://packagist.org/packages/votemike/money)
[![composer.lock](https://poser.pugx.org/votemike/money/composerlock)](https://packagist.org/packages/votemike/money)
[![StyleCI](https://styleci.io/repos/59353158/shield?branch=master)](https://styleci.io/repos/59353158)

Pass in an amount and currency to the Money object to create an immutable object. Perform actions with the object.
Deals with different units/rounding of different currencies.
Formats money for display
Please use GitHub to raise any issues and suggest any improvements.

## Install

Via Composer

``` bash
$ composer require votemike/money
```

## Usage

```
$money = new Money(99.999999, 'GBP');
$add = $money->add(new Money(20, 'GBP'));
$sub = $money->sub(new Money(20, 'GBP'));
$multiply = $money->multiply(3);
$divide = $money->divide(3);
$abs = $money->abs();
$inv = $money->inv();
$percentage = $money->percentage(20);
$round = $money->round();
list($first, $second, $third) = $money->split(20, 33.33);

$money = new Money(99.50, 'JPY');
$money->format(); //¥100
$money->formatWithSign(); //+¥100
$money->getAmount(); //99.50
$money->getCurrency(); //JPY
$money->getRoundedAmount(); //100

$money = new Money(9500, 'USD');
$money->formatShorthand(); //$10k

$money = new Money(9.500, 'USD');
$money->formatForAccounting(); //(9.50)
```

## Credits

- [Michael Gwynne](http://www.votemike.co.uk)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-author]: https://github.com/votemike
