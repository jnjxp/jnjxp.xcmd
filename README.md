# jnjxp.xcmd
Simple external command runner.
Basically just a `proc_open` wrapper;

[![Latest version][ico-version]][link-packagist]
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]

## Installation
```
composer require jnjxp/xcmd
```

## Usage
```php
use Jnjxp\Xcmd\ExternalCommand;

$cmd = new ExternalCommand('elinks -dump -dump-color-mode 1');
$payload = $cmd($response->getBody()); // write input to stdin

if ($payload->isError()) {
    foreach ($payload->getMessages() as $error) {
        echo $error . "\n";
    }
    exit($payload->getStatus())
}

echo $payload;

```


[ico-version]: https://img.shields.io/packagist/v/jnjxp/xcmd.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/jnjxp/jnjxp.xcmd/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/jnjxp/jnjxp.xcmd.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/jnjxp/jnjxp.xcmd.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/jnjxp/xcmd
[link-travis]: https://travis-ci.org/jnjxp/jnjxp.xcmd
[link-scrutinizer]: https://scrutinizer-ci.com/g/jnjxp/jnjxp.xcmd
[link-code-quality]: https://scrutinizer-ci.com/g/jnjxp/jnjxp.xcmd
