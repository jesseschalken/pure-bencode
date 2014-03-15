# pure-bencode

A simple, efficient and complete Bencode encoder/decoder for PHP.

### Usage

```php
use \PureBencode\Bencode;

// encode
$bencode = Bencode::encode($value);

// decode
$value   = Bencode::decode($bencode);
```

`Bencode::encode()` only accepts PHP values which can be converted into their exact original. Valid types are `int`, `string` and `array` containing more valid types.

Any problems will result in a `\PureBencode\Exception` with an appropriate message.
