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

use Exception;

abstract class AbstractString extends ASNObject implements Parsable
{
    private bool $checkStringForIllegalChars = true;
    private array $allowedCharacters = [];

    /**
     * The abstract base class for ASN.1 classes which represent some string of character.
     */
    public function __construct(protected string $value)
    {
    }

    public function getContent(): string
    {
        return $this->value;
    }

    protected function allowCharacter(string $character): void
    {
        $this->allowedCharacters[] = $character;
    }

    protected function allowCharacters(string ...$characters): void
    {
        foreach ($characters as $character) {
            $this->allowedCharacters[] = $character;
        }
    }

    protected function allowNumbers(): void
    {
        foreach (range('0', '9') as $char) {
            $this->allowedCharacters[] = (string) $char;
        }
    }

    protected function allowAllLetters(): void
    {
        $this->allowSmallLetters();
        $this->allowCapitalLetters();
    }

    protected function allowSmallLetters(): void
    {
        foreach (range('a', 'z') as $char) {
            $this->allowedCharacters[] = $char;
        }
    }

    protected function allowCapitalLetters(): void
    {
        foreach (range('A', 'Z') as $char) {
            $this->allowedCharacters[] = $char;
        }
    }

    protected function allowSpaces(): void
    {
        $this->allowedCharacters[] = ' ';
    }

    protected function allowAll(): void
    {
        $this->checkStringForIllegalChars = false;
    }

    protected function calculateContentLength(): int
    {
        return strlen($this->value);
    }

    protected function getEncodedValue(): ?string
    {
        if ($this->checkStringForIllegalChars) {
            $this->checkString();
        }

        return $this->value;
    }

    protected function checkString(): void
    {
        $stringLength = $this->getContentLength();
        for ($i = 0; $i < $stringLength; $i++) {
            if (in_array($this->value[$i], $this->allowedCharacters) === false) {
                $typeName = Identifier::getName($this->getType());
                throw new Exception("Could not create a {$typeName} from the character sequence '{$this->value}'.");
            }
        }
    }

    public static function fromBinary(string &$binaryData, ?int &$offsetIndex = 0): static
    {
        $parsedObject = new static('');

        self::parseIdentifier($binaryData[$offsetIndex], $parsedObject->getType(), $offsetIndex++);
        $contentLength = self::parseContentLength($binaryData, $offsetIndex);
        $string = substr($binaryData, $offsetIndex, $contentLength);
        $offsetIndex += $contentLength;

        $parsedObject->value = $string;
        $parsedObject->setContentLength($contentLength);
        return $parsedObject;
    }

    public static function isValid($string)
    {
        $testObject = new static($string);
        try {
            $testObject->checkString();

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
}
