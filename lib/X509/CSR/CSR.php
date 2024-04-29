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

namespace FG\X509\CSR;

use FG\ASN1\OID;
use FG\ASN1\Universal\Integer;
use FG\ASN1\Universal\BitString;
use FG\ASN1\Universal\Sequence;
use FG\X509\CertificateSubject;
use FG\X509\AlgorithmIdentifier;
use FG\X509\PublicKey;

class CSR extends Sequence
{
    const CSR_VERSION_NR = 0;
    private CertificateSubject $subject;

    public function __construct(
        string $commonName,
        string $email,
        string $organization,
        string $locality,
        string $state,
        string $country,
        string $organizationalUnit,
        private string $publicKey,
        private ?string $signature = null,
        private string $signatureAlgorithm = OID::SHA1_WITH_RSA_SIGNATURE
    ) {
        $this->subject = new CertificateSubject(
            $commonName,
            $email,
            $organization,
            $locality,
            $state,
            $country,
            $organizationalUnit
        );

        if ($signature !== null) {
            $this->createCSRSequence();
        }

        parent::__construct();
    }

    protected function createCSRSequence(): void
    {
        $versionNr            = new Integer(self::CSR_VERSION_NR);
        $publicKey            = new PublicKey($this->publicKey);
        $signature            = new BitString($this->signature);
        $signatureAlgorithm    = new AlgorithmIdentifier($this->signatureAlgorithm);

        $certRequestInfo  = new Sequence($versionNr, $this->subject, $publicKey);

        // Clear the underlying Construct
        $this->rewind();
        $this->children = [];
        $this->addChild($certRequestInfo);
        $this->addChild($signatureAlgorithm);
        $this->addChild($signature);
    }

    public function getSignatureSubject(): string
    {
        $versionNr            = new Integer(self::CSR_VERSION_NR);
        $publicKey            = new PublicKey($this->publicKey);

        $certRequestInfo  = new Sequence($versionNr, $this->subject, $publicKey);
        return $certRequestInfo->getBinary();
    }

    public function setSignature($signature, $signatureAlgorithm = OID::SHA1_WITH_RSA_SIGNATURE): void
    {
        $this->signature = $signature;
        $this->signatureAlgorithm = $signatureAlgorithm;

        $this->createCSRSequence();
    }

    public function __toString(): string
    {
        $tmp = base64_encode($this->getBinary());

        for ($i = 0; $i < strlen($tmp); $i++) {
            if (($i + 2) % 65 == 0) {
                $tmp = substr($tmp, 0, $i + 1)."\n".substr($tmp, $i + 1);
            }
        }

        $result = '-----BEGIN CERTIFICATE REQUEST-----'.PHP_EOL;
        $result .= $tmp.PHP_EOL;
        $result .= '-----END CERTIFICATE REQUEST-----';

        return $result;
    }

    public function getVersion(): int
    {
        return self::CSR_VERSION_NR;
    }

    public function getOrganizationName(): string
    {
        return $this->subject->getOrganization();
    }

    public function getLocalName(): string
    {
        return $this->subject->getLocality();
    }

    public function getState(): string
    {
        return $this->subject->getState();
    }

    public function getCountry(): string
    {
        return $this->subject->getCountry();
    }

    public function getOrganizationalUnit(): string
    {
        return $this->subject->getOrganizationalUnit();
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getSignatureAlgorithm(): string
    {
        return $this->signatureAlgorithm;
    }
}
