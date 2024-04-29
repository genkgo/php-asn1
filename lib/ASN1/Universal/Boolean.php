<?php
/*
 * This file is part of the PHPASN1 library.
 *
 * Copyright © Friedrich Große <friedrich.grosse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace FG\ASN1\Universal;

use FG\ASN1\ASNObject;
use FG\ASN1\Parsable;
use FG\ASN1\Identifier;
use FG\ASN1\Exception\ParserException;

class Boolean extends ASNObject implements Parsable
{
    public function __construct(private bool $value)
    {
    }

    public function getType(): int
    {
        return Identifier::BOOLEAN;
    }

    protected function calculateContentLength(): int
    {
        return 1;
    }

    protected function getEncodedValue(): string
    {
        if ($this->value === false) {
            return chr(0x00);
        } else {
            return chr(0xFF);
        }
    }

    public function getContent(): string
    {
        if ($this->value === true) {
            return 'TRUE';
        } else {
            return 'FALSE';
        }
    }

    public static function fromBinary(string &$binaryData, ?int &$offsetIndex = 0): static
    {
        self::parseIdentifier($binaryData[$offsetIndex], Identifier::BOOLEAN, $offsetIndex++);
        $contentLength = self::parseContentLength($binaryData, $offsetIndex);

        if ($contentLength != 1) {
            throw new ParserException("An ASN.1 Boolean should not have a length other than one. Extracted length was {$contentLength}", $offsetIndex);
        }

        $value = ord($binaryData[$offsetIndex++]);
        $booleanValue = $value == 0xFF ? true : false;

        $parsedObject = new self($booleanValue);
        $parsedObject->setContentLength($contentLength);

        return $parsedObject;
    }
}
