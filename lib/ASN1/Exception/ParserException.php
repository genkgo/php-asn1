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

namespace FG\ASN1\Exception;

class ParserException extends \Exception
{
    public function __construct(private string $errorMessage, private int $offset)
    {
        parent::__construct("ASN.1 Parser Exception at offset {$this->offset}: {$this->errorMessage}");
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
