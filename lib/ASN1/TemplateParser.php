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
use FG\ASN1\Exception\ParserException;
use FG\ASN1\Universal\Sequence;

class TemplateParser
{
    /**
     * @throws ParserException if there was an issue parsing
     */
    public function parseBase64(string $data, array $template): ASNObject|Sequence
    {
        // TODO test with invalid data
        return $this->parseBinary(base64_decode($data), $template);
    }

    /**
     * @throws ParserException if there was an issue parsing
     */
    public function parseBinary(string $binary, array $template): ASNObject|Sequence
    {
        $parsedObject = ASNObject::fromBinary($binary);

        foreach ($template as $key => $value) {
            $this->validate($parsedObject, $key, $value);
        }

        return $parsedObject;
    }

    private function validate(ASNObject $object, $key, $value): void
    {
        if (is_array($value)) {
            $this->assertTypeId($key, $object);

            /* @var Construct $object */
            foreach ($value as $key => $child) {
                $this->validate($object->current(), $key, $child);
                $object->next();
            }
        } else {
            $this->assertTypeId($value, $object);
        }
    }

    private function assertTypeId(int $expectedTypeId, ASNObject $object): void
    {
        $actualType = $object->getType();
        if ($expectedTypeId != $actualType) {
            throw new Exception("Expected type ($expectedTypeId) does not match actual type ($actualType");
        }
    }
}
