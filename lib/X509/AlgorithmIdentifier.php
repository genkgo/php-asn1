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

use FG\ASN1\Universal\NullObject;
use FG\ASN1\Composite\AttributeTypeAndValue;
use FG\ASN1\Universal\ObjectIdentifier;

class AlgorithmIdentifier extends AttributeTypeAndValue
{
    public function __construct(ObjectIdentifier|string $objectIdentifierString)
    {
        parent::__construct($objectIdentifierString, new NullObject());
    }
}
