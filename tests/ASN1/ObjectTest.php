<?php
/*
 * This file is part of the PHPASN1 library.
 *
 * Copyright © Friedrich Große <friedrich.grosse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FG\Test\ASN1;

use FG\ASN1\ExplicitlyTaggedObject;
use FG\ASN1\Universal\GeneralizedTime;
use FG\Test\ASN1TestCase;
use FG\ASN1\ASNObject;
use FG\ASN1\Exception\ParserException;
use FG\ASN1\UnknownConstructedObject;
use FG\ASN1\UnknownObject;
use FG\ASN1\Identifier;
use FG\ASN1\Universal\BitString;
use FG\ASN1\Universal\Boolean;
use FG\ASN1\Universal\Enumerated;
use FG\ASN1\Universal\Integer;
use FG\ASN1\Universal\NullObject;
use FG\ASN1\Universal\ObjectIdentifier;
use FG\ASN1\Universal\OctetString;
use FG\ASN1\Universal\Sequence;
use FG\ASN1\Universal\IA5String;
use FG\ASN1\Universal\PrintableString;

class ObjectTest extends ASN1TestCase
{
    public function testCalculateNumberOfLengthOctets()
    {
        $object = $this->getMockForAbstractClass('\FG\ASN1\ASNObject');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 32);
        $this->assertEquals(1, $calculatedNrOfLengthOctets);

        $object = $this->getMockForAbstractClass('\FG\ASN1\ASNObject');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 0);
        $this->assertEquals(1, $calculatedNrOfLengthOctets);

        $object = $this->getMockForAbstractClass('\FG\ASN1\ASNObject');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 127);
        $this->assertEquals(1, $calculatedNrOfLengthOctets);

        $object = $this->getMockForAbstractClass('\FG\ASN1\ASNObject');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 128);
        $this->assertEquals(2, $calculatedNrOfLengthOctets);

        $object = $this->getMockForAbstractClass('\FG\ASN1\ASNObject');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 255);
        $this->assertEquals(2, $calculatedNrOfLengthOctets);

        $object = $this->getMockForAbstractClass('\FG\ASN1\ASNObject');
        $calculatedNrOfLengthOctets = $this->callMethod($object, 'getNumberOfLengthOctets', 1025);
        $this->assertEquals(3, $calculatedNrOfLengthOctets);
    }

    /**
     * For the real parsing tests look in the test cases of each single ASN object.
     */
    public function testFromBinary()
    {
        /* @var BitString $parsedObject */
        $binaryData = chr(Identifier::BITSTRING);
        $binaryData .= chr(0x03);
        $binaryData .= chr(0x05);
        $binaryData .= chr(0xFF);
        $binaryData .= chr(0xA0);

        $expectedObject = new BitString(0xFFA0, 5);
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof BitString);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());
        $this->assertEquals($expectedObject->getNumberOfUnusedBits(), $parsedObject->getNumberOfUnusedBits());

        /* @var OctetString $parsedObject */
        $binaryData = chr(Identifier::OCTETSTRING);
        $binaryData .= chr(0x02);
        $binaryData .= chr(0xFF);
        $binaryData .= chr(0xA0);

        $expectedObject = new OctetString(0xFFA0);
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof OctetString);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /* @var \FG\ASN1\Universal\Boolean $parsedObject */
        $binaryData = chr(Identifier::BOOLEAN);
        $binaryData .= chr(0x01);
        $binaryData .= chr(0xFF);

        $expectedObject = new Boolean(true);
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof Boolean);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /* @var Enumerated $parsedObject */
        $binaryData = chr(Identifier::ENUMERATED);
        $binaryData .= chr(0x01);
        $binaryData .= chr(0x03);

        $expectedObject = new Enumerated(3);
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof Enumerated);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /* @var IA5String $parsedObject */
        $string = 'Hello Foo World!!!11EinsEins!1';
        $binaryData = chr(Identifier::IA5_STRING);
        $binaryData .= chr(strlen($string));
        $binaryData .= $string;

        $expectedObject = new IA5String($string);
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof IA5String);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /* @var \FG\ASN1\Universal\Integer $parsedObject */
        $binaryData = chr(Identifier::INTEGER);
        $binaryData .= chr(0x01);
        $binaryData .= chr(123);

        $expectedObject = new Integer(123);
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof Integer);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /* @var \FG\ASN1\Universal\NullObject $parsedObject */
        $binaryData = chr(Identifier::NULL);
        $binaryData .= chr(0x00);

        $expectedObject = new NullObject();
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof NullObject);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /* @var ObjectIdentifier $parsedObject */
        $binaryData = chr(Identifier::OBJECT_IDENTIFIER);
        $binaryData .= chr(0x02);
        $binaryData .= chr(1 * 40 + 2);
        $binaryData .= chr(3);

        $expectedObject = new ObjectIdentifier('1.2.3');
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof ObjectIdentifier);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /* @var PrintableString $parsedObject */
        $string = 'This is a test string. #?!%&""';
        $binaryData = chr(Identifier::PRINTABLE_STRING);
        $binaryData .= chr(strlen($string));
        $binaryData .= $string;

        $expectedObject = new PrintableString($string);
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof PrintableString);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /* @var GeneralizedTime $parsedObject */
        $binaryData  = chr(Identifier::GENERALIZED_TIME);
        $binaryData .= chr(15);
        $binaryData .= '20120923202316Z';

        $expectedObject = new GeneralizedTime('2012-09-23 20:23:16', 'UTC');
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof GeneralizedTime);
        $this->assertEquals($expectedObject->getContent(), $parsedObject->getContent());

        /* @var Sequence $parsedObject */
        $binaryData = chr(Identifier::SEQUENCE);
        $binaryData .= chr(0x06);
        $binaryData .= chr(Identifier::BOOLEAN);
        $binaryData .= chr(0x01);
        $binaryData .= chr(0x00);
        $binaryData .= chr(Identifier::INTEGER);
        $binaryData .= chr(0x01);
        $binaryData .= chr(0x03);

        $expectedChild1 = new Boolean(false);
        $expectedChild2 = new Integer(0x03);

        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof Sequence);
        $this->assertEquals(2, $parsedObject->getNumberOfChildren());

        $children = $parsedObject->getChildren();
        $child1 = $children[0];
        $child2 = $children[1];
        $this->assertEquals($expectedChild1->getContent(), $child1->getContent());
        $this->assertEquals($expectedChild2->getContent(), $child2->getContent());

        /* @var ExplicitlyTaggedObject $parsedObject */
        $taggedObject = new ExplicitlyTaggedObject(0x01, new PrintableString('Hello tagged world'));
        $binaryData = $taggedObject->getBinary();
        $parsedObject = ASNObject::fromBinary($binaryData);
        $this->assertTrue($parsedObject instanceof ExplicitlyTaggedObject);

        // An unknown constructed object containing 2 integer children,
        // first 3 bytes are the identifier.
        $binaryData = "\x3F\x81\x7F\x06".chr(Identifier::INTEGER)."\x01\x42".chr(Identifier::INTEGER)."\x01\x69";
        $offsetIndex = 0;
        $parsedObject = ASNObject::fromBinary($binaryData, $offsetIndex);
        $this->assertTrue($parsedObject instanceof UnknownConstructedObject);
        $this->assertEquals(substr($binaryData, 0, 3), $parsedObject->getIdentifier());
        $this->assertCount(2, $parsedObject->getContent());
        $this->assertEquals(strlen($binaryData), $offsetIndex);
        $this->assertEquals(10, $parsedObject->getObjectLength());

        // First 3 bytes are the identifier
        $binaryData = "\x1F\x81\x7F\x01\xFF";
        $offsetIndex = 0;
        $parsedObject = ASNObject::fromBinary($binaryData, $offsetIndex);
        $this->assertTrue($parsedObject instanceof UnknownObject);
        $this->assertEquals(substr($binaryData, 0, 3), $parsedObject->getIdentifier());
        $this->assertEquals('Unparsable Object (1 bytes)', $parsedObject->getContent());
        $this->assertEquals(strlen($binaryData), $offsetIndex);
        $this->assertEquals(5, $parsedObject->getObjectLength());
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryThrowsException()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 10: Can not parse binary from data: Offset index larger than input size");

        $binaryData = 0x0;
        $offset = 10;
        ASNObject::fromBinary($binaryData, $offset);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithEmptyStringThrowsException()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 0: Can not parse binary from data: Offset index larger than input size");

        $data = '';
        ASNObject::fromBinary($data);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithSpacyStringThrowsException()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 2: Content length 50 exceeds remaining data length 1");

        $data = "\x32\x32\x32";
        ASNObject::fromBinary($data);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithNumberStringThrowsException()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 1: Can not parse content length from data: Offset index larger than input size");

        $data = '1';
        ASNObject::fromBinary($data);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithGarbageStringThrowsException()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 2: Content length 101 exceeds remaining data length 23");

        $data = 'certainly no asn.1 object';
        ASNObject::fromBinary($data);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryUnknownObjectMissingLength()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 1: Can not parse identifier (long form) from data: Offset index larger than input size");

        $data = hex2bin('1f');
        ASNObject::fromBinary($data);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryInalidLongFormContentLength()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 4: Can not parse content length (long form) from data: Offset index larger than input size");

        $binaryData  = chr(Identifier::INTEGER);
        $binaryData .= chr(0x8f); //denotes a long-form content length with 15 length-octets
        $binaryData .= chr(0x1);  //only give one content-length-octet
        $binaryData .= chr(0x1);  //this is needed to reach the code to be tested

        ASNObject::fromBinary($binaryData);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryExceedsMaxInt()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 11: Can not parse content length from data: length > maximum integer");

        $bin = hex2bin("308901000000000000004502202ba3a8be6b94d5ec80a6d9d1190a436effe50d85a1eee859b8cc6af9bd5c2e18022100b329f479a2bbd0a5c384ee1493b1f5186a87139cac5df4087c134b49156847db");
        ASNObject::fromBinary($bin);
    }

    public function testWithLeadingZeroes()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 3: Content length cannot have leading zero bytes");
        $bin = hex2bin('30820066023100814cc9a70febda342d4ada87fc39426f403d5e89808428460c1eca60c897bfd6728da14673854673d7d297ea944a15e202310084f5ef11d22f22d0548af6a50dbf2f6a1bb9054585af5e600c49cf35b1e69b712754dd781c837355ddd41c752193a7cd');
        ASNObject::fromBinary($bin);
    }

    public function testExtendedFormShortLength()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('ASN.1 Parser Exception at offset 3: Extended length used for short message');
        $bin = hex2bin('30814502202ba3a8be6b94d5ec80a6d9d1190a436effe50d85a1eee859b8cc6af9bd5c2e18022100b329f479a2bbd0a5c384ee1493b1f5186a87139cac5df4087c134b49156847db');
        ASNObject::fromBinary($bin);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithInconsistentLength()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 2: Content length 1 exceeds remaining data length 0");

        $binaryData  = chr(Identifier::NULL);
        $binaryData .= chr(0x01);
        ASNObject::fromBinary($binaryData);
    }
}
