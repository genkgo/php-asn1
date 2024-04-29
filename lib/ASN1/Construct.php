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

use ArrayAccess;
use ArrayIterator;
use Countable;
use FG\ASN1\Exception\ParserException;
use Iterator;

abstract class Construct extends ASNObject implements Countable, ArrayAccess, Iterator, Parsable
{
    /** @var ASNObject[] */
    protected array $children;
    private int $iteratorPosition = 0;

    /**
     * @param ASNObject[] $children the variadic type hint is commented due to https://github.com/facebook/hhvm/issues/4858
     */
    public function __construct(ASNObject ...$children)
    {
        $this->children = $children;
    }

    public function getContent(): mixed
    {
        return $this->children;
    }

    public function rewind(): void
    {
        $this->iteratorPosition = 0;
    }

    public function current(): ASNObject
    {
        return $this->children[$this->iteratorPosition];
    }

    public function key(): int
    {
        return $this->iteratorPosition;
    }

    public function next(): void
    {
        $this->iteratorPosition++;
    }

    public function valid(): bool
    {
        return isset($this->children[$this->iteratorPosition]);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->children);
    }

    public function offsetGet($offset): ASNObject
    {
        return $this->children[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $offset = count($this->children);
        }

        $this->children[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->children[$offset]);
    }

    protected function calculateContentLength(): int
    {
        $length = 0;
        foreach ($this->children as $component) {
            $length += $component->getObjectLength();
        }

        return $length;
    }

    protected function getEncodedValue(): ?string
    {
        $result = '';
        foreach ($this->children as $component) {
            $result .= $component->getBinary();
        }

        return $result;
    }

    public function addChild(ASNObject $child): void
    {
        $this->children[] = $child;
    }

    public function addChildren(array $children): void
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }

    public function __toString(): string
    {
        $nrOfChildren = $this->getNumberOfChildren();
        $childString = $nrOfChildren == 1 ? 'child' : 'children';

        return "[{$nrOfChildren} {$childString}]";
    }

    public function getNumberOfChildren(): int
    {
        return count($this->children);
    }

    /**
     * @return ASNObject[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return ASNObject
     */
    public function getFirstChild(): ASNObject
    {
        return $this->children[0];
    }

    /**
     * @param string $binaryData
     * @param int $offsetIndex
     *
     * @throws Exception\ParserException
     *
     * @return Construct|static
     */
    public static function fromBinary(string &$binaryData, ?int &$offsetIndex = 0): static
    {
        $parsedObject = new static();
        self::parseIdentifier($binaryData[$offsetIndex], $parsedObject->getType(), $offsetIndex++);
        $contentLength = self::parseContentLength($binaryData, $offsetIndex);
        $startIndex = $offsetIndex;

        $children = [];
        $octetsToRead = $contentLength;
        while ($octetsToRead > 0) {
            $newChild = ASNObject::fromBinary($binaryData, $offsetIndex);
            $octetsToRead -= $newChild->getObjectLength();
            $children[] = $newChild;
        }

        if ($octetsToRead !== 0) {
            throw new ParserException("Sequence length incorrect", $startIndex);
        }

        $parsedObject->addChildren($children);
        $parsedObject->setContentLength($contentLength);

        return $parsedObject;
    }

    public function count($mode = COUNT_NORMAL): int
    {
        return count($this->children, $mode);
    }

    public function getIterator(): \ArrayIterator
    {
        return new ArrayIterator($this->children);
    }
}