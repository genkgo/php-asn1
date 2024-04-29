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

use FG\ASN1\Identifier;

class Enumerated extends Integer
{
    public function getType(): int
    {
        return Identifier::ENUMERATED;
    }
}
