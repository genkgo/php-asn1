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

use FG\ASN1\AbstractString;
use FG\ASN1\Identifier;

class VisibleString extends AbstractString
{
    /**
     * Creates a new ASN.1 Visible String.
     * TODO The encodable characters of this type are not yet checked.
     */
    public function __construct(string $string)
    {
        parent::__construct($string);
        $this->allowAll();
    }

    public function getType(): int
    {
        return Identifier::VISIBLE_STRING;
    }
}
