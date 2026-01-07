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

use FG\ASN1\Exception\ParserException;
use FG\ASN1\OID;
use FG\ASN1\ASNObject;
use FG\ASN1\Parsable;
use FG\ASN1\Identifier;
use FG\ASN1\Universal\OctetString;
use FG\ASN1\Universal\Set;
use FG\ASN1\Universal\Sequence;
use FG\ASN1\Universal\ObjectIdentifier;
use FG\X509\SAN\SubjectAlternativeNames;

class CertificateExtensions extends Set implements Parsable
{
    private Sequence $innerSequence;
    private array $extensions = [];

    public function __construct()
    {
        $this->innerSequence = new Sequence();
        parent::__construct($this->innerSequence);
    }

    public function addSubjectAlternativeNames(SubjectAlternativeNames $sans): void
    {
        $this->addExtension(OID::CERT_EXT_SUBJECT_ALT_NAME, $sans);
    }

    private function addExtension(string $oidString, ASNObject $extension): void
    {
        $sequence = new Sequence();
        $sequence->addChild(new ObjectIdentifier($oidString));
        $sequence->addChild($extension);

        $this->innerSequence->addChild($sequence);
        $this->extensions[] = $extension;
    }

    public function getContent(): array
    {
        return $this->extensions;
    }

    public static function fromBinary(string &$binaryData, ?int &$offsetIndex = 0): static
    {
        self::parseIdentifier($binaryData[$offsetIndex], Identifier::SET, $offsetIndex++);
        self::parseContentLength($binaryData, $offsetIndex);

        $tmpOffset = $offsetIndex;
        $extensions = Sequence::fromBinary($binaryData, $offsetIndex);
        $tmpOffset += 1 + $extensions->getNumberOfLengthOctets();

        $parsedObject = new self();
        foreach ($extensions as $extension) {
            if ($extension->getType() != Identifier::SEQUENCE) {
                //FIXME wrong offset index
                throw new ParserException('Could not parse Certificate Extensions: Expected ASN.1 Sequence but got '.$extension->getTypeName(), $offsetIndex);
            }

            $tmpOffset += 1 + $extension->getNumberOfLengthOctets();
            $children = $extension->getChildren();
            if (count($children) < 2) {
                throw new ParserException('Could not parse Certificate Extensions: Needs at least two child elements per extension sequence (object identifier and octet string)', $tmpOffset);
            }
            /** @var \FG\ASN1\ASNObject $objectIdentifier */
            $objectIdentifier = $children[0];

            /** @var OctetString $octetString */
            $octetString = $children[1];

            if ($objectIdentifier->getType() != Identifier::OBJECT_IDENTIFIER) {
                throw new ParserException('Could not parse Certificate Extensions: Expected ASN.1 Object Identifier but got '.$extension->getTypeName(), $tmpOffset);
            }

            $tmpOffset += $objectIdentifier->getObjectLength();

            if ($objectIdentifier->getContent() == OID::CERT_EXT_SUBJECT_ALT_NAME) {
                $sans = SubjectAlternativeNames::fromBinary($binaryData, $tmpOffset);
                $parsedObject->addSubjectAlternativeNames($sans);
            } else {
                // can now only parse SANs. There might be more in the future
                $tmpOffset += $octetString->getObjectLength();
            }
        }

        $parsedObject->getBinary(); // Determine the number of content octets and object sizes once (just to let the equality unit tests pass :/ )
        return $parsedObject;
    }
}
