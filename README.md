# pure-bencode

A simple, efficient and complete Bencode encoder/decoder for PHP.

### Installation

Install [the composer package](https://packagist.org/packages/pure-bencode/pure-bencode).

### Usage

```php
use \PureBencode\Bencode;

// encode
$bencode = Bencode::encode($value);

// decode
$value   = Bencode::decode($bencode);
```

- `Bencode::encode()` accepts only PHP values which can be converted to their exact original by `Bencode::decode()`. Those are `int`s, `string`s and `array`s whose contents are also valid.
- `array`s with keys of the form *0, 1, 2...n* with be encoded as lists. All others will be encoded as dictionaries with their keys in sorted order.
- Any problems will result in a `\PureBencode\Exception` with an appropriate message.

That is all. Have fun `Bencode`ing!
