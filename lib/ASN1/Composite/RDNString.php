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

namespace FG\ASN1\Composite;

use FG\ASN1\ASNObject;
use FG\ASN1\Universal\ObjectIdentifier;
use FG\ASN1\Universal\PrintableString;
use FG\ASN1\Universal\IA5String;
use FG\ASN1\Universal\UTF8String;

class RDNString extends RelativeDistinguishedName
{
    public function __construct(string|ObjectIdentifier $objectIdentifierString, string|ASNObject $value)
    {
        if (PrintableString::isValid($value)) {
            $value = new PrintableString($value);
        } else {
            if (IA5String::isValid($value)) {
                $value = new IA5String($value);
            } else {
                $value = new UTF8String($value);
            }
        }

        parent::__construct($objectIdentifierString, $value);
    }
}
