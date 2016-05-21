# Money

[![Build Status](https://travis-ci.org/votemike/money.svg?branch=master)](https://travis-ci.org/votemike/money)

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
$money->getAmount(); //99.50
$money->getCurrency(); //JPY
$money->getRoundedAmount(); //100
```

## Credits

- [Michael Gwynne](http://www.votemike.co.uk)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-author]: https://github.com/votemike
