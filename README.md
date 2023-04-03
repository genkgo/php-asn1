PHPASN1
=======

[![Build Status](https://github.com/genkgo/php-asn1/actions/workflows/phpunit.yml/badge.svg)](https://github.com/genkgo/php-asn1/actions/workflows/phpunit.yml)
---

A PHP Framework that allows you to encode and decode arbitrary [ASN.1][3] structures
using the [ITU-T X.690 Encoding Rules][4].
This encoding is very frequently used in [X.509 PKI environments][5] or the communication between heterogeneous computer systems.

The API allows you to encode ASN.1 structures to create binary data such as certificate
signing requests (CSR), X.509 certificates or certificate revocation lists (CRL).
PHPASN1 can also read [BER encoded][6] binary data into separate PHP objects that can be manipulated by the user and reencoded afterwards.

The **changelog** can now be found at [CHANGELOG.md](CHANGELOG.md).

## Dependencies

PHPASN1 requires at [a PHP versions receiving security support](https://www.php.net/supported-versions.php) and either the `gmp` or `bcmath` extension.

Support for older PHP versions:
- For PHP version 5 use `v1.x`.
- For PHP version 7 use `v2.5.x`.

For the loading of object identifier names directly from the web [curl][7] is used.

## Installation

The preferred way to install this library is to rely on [Composer][2]:

```bash
$ composer require genkgo/php-asn1
```

## Usage

### Encoding ASN.1 Structures

PHPASN1 offers you a class for each of the implemented ASN.1 universal types.
The constructors should be pretty self explanatory so you should have no big trouble getting started.
All data will be encoded using [DER encoding][8]

```php
use FG\ASN1\OID;
use FG\ASN1\Universal\Integer;
use FG\ASN1\Universal\Boolean;
use FG\ASN1\Universal\Enumerated;
use FG\ASN1\Universal\IA5String;
use FG\ASN1\Universal\ObjectIdentifier;
use FG\ASN1\Universal\PrintableString;
use FG\ASN1\Universal\Sequence;
use FG\ASN1\Universal\Set;
use FG\ASN1\Universal\NullObject;

$integer = new Integer(123456);        
$boolean = new Boolean(true);
$enum = new Enumerated(1);
$ia5String = new IA5String('Hello world');

$asnNull = new NullObject();
$objectIdentifier1 = new ObjectIdentifier('1.2.250.1.16.9');
$objectIdentifier2 = new ObjectIdentifier(OID::RSA_ENCRYPTION);
$printableString = new PrintableString('Foo bar');

$sequence = new Sequence($integer, $boolean, $enum, $ia5String);
$set = new Set($sequence, $asnNull, $objectIdentifier1, $objectIdentifier2, $printableString);

$myBinary  = $sequence->getBinary();
$myBinary .= $set->getBinary();

echo base64_encode($myBinary);
```


### Decoding binary data

Decoding BER encoded binary data is just as easy as encoding it:

```php
use FG\ASN1\ASNObject;

$base64String = ...
$binaryData = base64_decode($base64String);        
$asnObject = ASNObject::fromBinary($binaryData);


// do stuff
```

If you already know exactly how your expected data should look like you can use the `FG\ASN1\TemplateParser`:

```php
use FG\ASN1\TemplateParser;

// first define your template
$template = [
    Identifier::SEQUENCE => [
        Identifier::SET => [
            Identifier::OBJECT_IDENTIFIER,
            Identifier::SEQUENCE => [
                Identifier::INTEGER,
                Identifier::BITSTRING,
            ]
        ]
    ]
];

// if your binary data is not matching the template you provided this will throw an `\Exception`:
$parser = new TemplateParser();
$object = $parser->parseBinary($data, $template);

// there is also a convenience function if you parse binary data from base64:
$object = $parser->parseBase64($data, $template);
```

You can use this function to make sure your data has exactly the format you are expecting.

### Navigating decoded data

All constructed classes (i.e. `Sequence` and `Set`) can be navigated by array access or using an iterator.
You can find examples
[here](https://github.com/genkgo/php-asn1/blob/f6442cadda9d36f3518c737e32f28300a588b777/tests/ASN1/Universal/SequenceTest.php#L148-148),
[here](https://github.com/genkgo/php-asn1/blob/f6442cadda9d36f3518c737e32f28300a588b777/tests/ASN1/Universal/SequenceTest.php#L121) and 
[here](https://github.com/genkgo/php-asn1/blob/f6442cadda9d36f3518c737e32f28300a588b777/tests/ASN1/TemplateParserTest.php#L45).


### Give me more examples!

To see some example usage of the API classes or some generated output check out the [examples](https://github.com/genkgo/php-asn1/tree/master/examples).

## Contributing

### How do I contribute?

This project is no longer maintained and thus does not accept any new contributions.

### Historical contributors

Huge thanks to Friedrich Große. He maintained this library for 11 years. And of course to [all contributors][1] so far!

## License

This library is distributed under the [MIT License](LICENSE).

[1]: https://github.com/genkgo/php-asn1/graphs/contributors
[2]: https://getcomposer.org/
[3]: http://www.itu.int/ITU-T/asn1/
[4]: http://www.itu.int/ITU-T/recommendations/rec.aspx?rec=x.690
[5]: http://en.wikipedia.org/wiki/X.509
[6]: http://en.wikipedia.org/wiki/X.690#BER_encoding
[7]: http://php.net/manual/en/book.curl.php
[8]: http://en.wikipedia.org/wiki/X.690#DER_encoding
