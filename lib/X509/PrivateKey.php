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

namespace FG\X509;

use FG\ASN1\ASNObject;
use FG\ASN1\OID;
use FG\ASN1\Universal\NullObject;
use FG\ASN1\Universal\Sequence;
use FG\ASN1\Universal\BitString;
use FG\ASN1\Universal\ObjectIdentifier;

class PrivateKey extends Sequence
{
    public function __construct(string $hexKey, ASNObject|string $algorithmIdentifierString = OID::RSA_ENCRYPTION)
    {
        parent::__construct(
            new Sequence(
                new ObjectIdentifier($algorithmIdentifierString),
                new NullObject()
            ),
            new BitString($hexKey)
        );
    }
}
