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

use FG\ASN1\Composite\RelativeDistinguishedName;
use FG\ASN1\Identifier;
use FG\ASN1\OID;
use FG\ASN1\Parsable;
use FG\ASN1\Composite\RDNString;
use FG\ASN1\Universal\Sequence;

class CertificateSubject extends Sequence implements Parsable
{
    public function __construct(
        private string $commonName,
        private string $email,
        private string $organization,
        private string $locality,
        private string $state,
        private string $country,
        private string $organizationalUnit
    ) {
        parent::__construct(
            new RDNString(OID::COUNTRY_NAME, $country),
            new RDNString(OID::STATE_OR_PROVINCE_NAME, $state),
            new RDNString(OID::LOCALITY_NAME, $locality),
            new RDNString(OID::ORGANIZATION_NAME, $organization),
            new RDNString(OID::OU_NAME, $organizationalUnit),
            new RDNString(OID::COMMON_NAME, $commonName),
            new RDNString(OID::PKCS9_EMAIL, $email)
        );
    }

    public function getCommonName(): string
    {
        return $this->commonName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getOrganization(): string
    {
        return $this->organization;
    }

    public function getLocality(): string
    {
        return $this->locality;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getOrganizationalUnit(): string
    {
        return $this->organizationalUnit;
    }

    public static function fromBinary(string &$binaryData, ?int &$offsetIndex = 0): static
    {
        self::parseIdentifier($binaryData[$offsetIndex], Identifier::SEQUENCE, $offsetIndex++);
        $contentLength = self::parseContentLength($binaryData, $offsetIndex);

        $names = [];
        $octetsToRead = $contentLength;
        while ($octetsToRead > 0) {
            $relativeDistinguishedName = RelativeDistinguishedName::fromBinary($binaryData, $offsetIndex);
            $octetsToRead -= $relativeDistinguishedName->getObjectLength();
            $names[] = $relativeDistinguishedName;
        }
    }
}
