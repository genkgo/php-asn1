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

namespace FG\ASN1;

class UnknownObject extends ASNObject
{
    private string $value;
    private string $identifier;

    /**
     * @param string|int $identifier Either the first identifier octet as int or all identifier bytes as a string
     */
    public function __construct(string|int $identifier, int $contentLength)
    {
        if (is_int($identifier)) {
            $identifier = chr($identifier);
        }

        $this->identifier = $identifier;
        $this->value = "Unparsable Object ({$contentLength} bytes)";
        $this->setContentLength($contentLength);
    }

    public function getContent(): string
    {
        return $this->value;
    }

    public function getType(): int
    {
        return ord($this->identifier[0]);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    protected function calculateContentLength(): int
    {
        return $this->getContentLength();
    }

    protected function getEncodedValue(): ?string
    {
        return '';
    }
}
