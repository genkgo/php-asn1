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

class UnknownConstructedObject extends Construct
{
    private string $identifier;
    private int $contentLength;

    /**
     * @param string $binaryData
     * @param int $offsetIndex
     *
     * @throws \FG\ASN1\Exception\ParserException
     */
    public function __construct(string $binaryData, int &$offsetIndex)
    {
        $this->identifier = self::parseBinaryIdentifier($binaryData, $offsetIndex);
        $this->contentLength = self::parseContentLength($binaryData, $offsetIndex);

        $children = [];
        $octetsToRead = $this->contentLength;
        while ($octetsToRead > 0) {
            $newChild = ASNObject::fromBinary($binaryData, $offsetIndex);
            $octetsToRead -= $newChild->getObjectLength();
            $children[] = $newChild;
        }

        parent::__construct(...$children);
    }

    public function getType(): int
    {
        return ord($this->identifier);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    protected function calculateContentLength(): int
    {
        return $this->contentLength;
    }

    protected function getEncodedValue(): ?string
    {
        return '';
    }
}
