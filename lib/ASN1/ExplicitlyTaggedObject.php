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

use FG\ASN1\Exception\ParserException;

/**
 * Class ExplicitlyTaggedObject decorate an inner object with an additional tag that gives information about
 * its context specific meaning.
 *
 * Explanation taken from A Layman's Guide to a Subset of ASN.1, BER, and DER:
 * >>> An RSA Laboratories Technical Note
 * >>> Burton S. Kaliski Jr.
 * >>> Revised November 1, 1993
 *
 * [...]
 * Explicitly tagged types are derived from other types by adding an outer tag to the underlying type.
 * In effect, explicitly tagged types are structured types consisting of one component, the underlying type.
 * Explicit tagging is denoted by the ASN.1 keywords [class number] EXPLICIT (see Section 5.2).
 * [...]
 *
 * @see http://luca.ntop.org/Teaching/Appunti/asn1.html
 */
class ExplicitlyTaggedObject extends ASNObject
{
    /** @var ASNObject[] */
    private array $decoratedObjects;

    public function __construct(private string|int $tag, ASNObject ...$objects)
    {
        $this->decoratedObjects = $objects;
    }

    protected function calculateContentLength(): int
    {
        $length = 0;
        foreach ($this->decoratedObjects as $object) {
            $length += $object->getObjectLength();
        }

        return $length;
    }

    protected function getEncodedValue(): ?string
    {
        $encoded = '';
        foreach ($this->decoratedObjects as $object) {
            $encoded .= $object->getBinary();
        }

        return $encoded;
    }

    public function getContent(): array
    {
        return $this->decoratedObjects;
    }

    public function __toString(): string
    {
        switch ($length = count($this->decoratedObjects)) {
        case 0:
            return "Context specific empty object with tag [{$this->tag}]";
        case 1:
            $decoratedType = Identifier::getShortName($this->decoratedObjects[0]->getType());
            return "Context specific $decoratedType with tag [{$this->tag}]";
        default:
            return "$length context specific objects with tag [{$this->tag}]";
        }
    }

    public function getType(): int
    {
        return ord($this->getIdentifier());
    }

    public function getIdentifier(): string
    {
        $identifier = Identifier::create(Identifier::CLASS_CONTEXT_SPECIFIC, true, $this->tag);

        return is_int($identifier) ? chr($identifier) : $identifier;
    }

    public function getTag(): int
    {
        return $this->tag;
    }

    public static function fromBinary(string &$binaryData, ?int &$offsetIndex = 0): static
    {
        $identifier = self::parseBinaryIdentifier($binaryData, $offsetIndex);
        $firstIdentifierOctet = ord($identifier);
        assert(Identifier::isContextSpecificClass($firstIdentifierOctet), 'identifier octet should indicate context specific class');
        assert(Identifier::isConstructed($firstIdentifierOctet), 'identifier octet should indicate constructed object');
        $tag = Identifier::getTagNumber($identifier);

        $totalContentLength = self::parseContentLength($binaryData, $offsetIndex);
        $remainingContentLength = $totalContentLength;

        $offsetIndexOfDecoratedObject = $offsetIndex;
        $decoratedObjects = [];

        while ($remainingContentLength > 0) {
            $nextObject = ASNObject::fromBinary($binaryData, $offsetIndex);
            $remainingContentLength -= $nextObject->getObjectLength();
            $decoratedObjects[] = $nextObject;
        }

        if ($remainingContentLength != 0) {
            throw new ParserException("Context-Specific explicitly tagged object [$tag] starting at offset $offsetIndexOfDecoratedObject specifies a length of $totalContentLength octets but $remainingContentLength remain after parsing the content", $offsetIndexOfDecoratedObject);
        }

        $parsedObject = new self($tag, ...$decoratedObjects);
        $parsedObject->setContentLength($totalContentLength);
        return $parsedObject;
    }
}
