<?php
/*
 * This file is part of the PHPASN1 library.
 *
 * Copyright © Friedrich Große <friedrich.grosse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FG\Test\ASN1\Universal;

use FG\Utility\BigInteger;
use FG\ASN1\ASNObject;
use FG\ASN1\Exception\ParserException;
use FG\Test\ASN1TestCase;
use FG\ASN1\Identifier;
use FG\ASN1\Universal\Integer;

class IntegerTest extends ASN1TestCase
{
    public function testGetType()
    {
        $object = new Integer(123);
        $this->assertEquals(Identifier::INTEGER, $object->getType());
    }

    public function testGetIdentifier()
    {
        $object = new Integer(123);
        $this->assertEquals(chr(Identifier::INTEGER), $object->getIdentifier());
    }

    public function testCreateInstanceCanFail()
    {
        $this->expectException(\Exception::class);

        new Integer('a');
    }

    public function testContent()
    {
        $object = new Integer(1234);
        $this->assertEquals(1234, $object->getContent());

        $object = new Integer(-1234);
        $this->assertEquals(-1234, $object->getContent());

        $object = new Integer(0);
        $this->assertEquals(0, $object->getContent());

        // test with maximum integer value
        $object = new Integer(PHP_INT_MAX);
        $this->assertEquals(PHP_INT_MAX, $object->getContent());

        // test with minimum integer value by negating the max value
        $object = new Integer(~PHP_INT_MAX);
        $this->assertEquals(~PHP_INT_MAX, $object->getContent());
    }

    public function testGetObjectLength()
    {
        $positiveObj = new Integer(0);
        $expectedSize = 2 + 1;
        $this->assertEquals($expectedSize, $positiveObj->getObjectLength());

        $positiveObj = new Integer(127);
        $negativeObj = new Integer(-127);
        $expectedSize = 2 + 1;
        $this->assertEquals($expectedSize, $positiveObj->getObjectLength());
        $this->assertEquals($expectedSize, $negativeObj->getObjectLength());

        $positiveObj = new Integer(128);
        $expectedSize = 2 + 2;
        $this->assertEquals($expectedSize, $positiveObj->getObjectLength());

        $negativeObj = new Integer(-128);
        $expectedSize = 2 + 1;
        $this->assertEquals($expectedSize, $negativeObj->getObjectLength());

        $positiveObj = new Integer(0x7FFF);
        $negativeObj = new Integer(-0x7FFF);
        $expectedSize = 2 + 2;
        $this->assertEquals($expectedSize, $positiveObj->getObjectLength());
        $this->assertEquals($expectedSize, $negativeObj->getObjectLength());

        $positiveObj = new Integer(0x8000);
        $expectedSize = 2 + 3;
        $this->assertEquals($expectedSize, $positiveObj->getObjectLength());

        $negativeObj = new Integer(-0x8000);
        $expectedSize = 2 + 2;
        $this->assertEquals($expectedSize, $negativeObj->getObjectLength());

        $positiveObj = new Integer(0x7FFFFF);
        $negativeObj = new Integer(-0x7FFFFF);
        $expectedSize = 2 + 3;
        $this->assertEquals($expectedSize, $positiveObj->getObjectLength());
        $this->assertEquals($expectedSize, $negativeObj->getObjectLength());

        $positiveObj = new Integer(0x800000);
        $expectedSize = 2 + 4;
        $this->assertEquals($expectedSize, $positiveObj->getObjectLength());

        $negativeObj = new Integer(-0x800000);
        $expectedSize = 2 + 3;
        $this->assertEquals($expectedSize, $negativeObj->getObjectLength());

        $positiveObj = new Integer(0x7FFFFFFF);
        $negativeObj = new Integer(-0x7FFFFFFF);
        $expectedSize = 2 + 4;
        $this->assertEquals($expectedSize, $positiveObj->getObjectLength());
        $this->assertEquals($expectedSize, $negativeObj->getObjectLength());
    }

    public function testGetBinary()
    {
        $expectedType = chr(Identifier::INTEGER);
        $expectedLength = chr(0x01);

        $object = new Integer(0);
        $expectedContent = chr(0x00);
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $object->getBinary());

        $object = new Integer(127);
        $expectedContent = chr(0x7F);
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $object->getBinary());

        $object = new Integer(-127);
        $expectedContent = chr(0x81);
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $object->getBinary());

        $object = new Integer(200);
        $expectedLength = chr(0x02);
        $expectedContent = chr(0x00);
        $expectedContent .= chr(0xC8);
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $object->getBinary());

        $object = new Integer(-546);
        $expectedLength = chr(0x02);
        $expectedContent = chr(0xFD);
        $expectedContent .= chr(0xDE);
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $object->getBinary());

        $object = new Integer(7420);
        $expectedLength   = chr(0x02);
        $expectedContent  = chr(0x1C);
        $expectedContent .= chr(0xFC);
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $object->getBinary());

        $object = new Integer(-1891004);
        $expectedLength   = chr(0x03);
        $expectedContent  = chr(0xE3);
        $expectedContent .= chr(0x25);
        $expectedContent .= chr(0x44);
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $object->getBinary());
    }

    public function testBigIntegerSupport()
    {
        // Positive bigint
        $expectedType     = chr(Identifier::INTEGER);
        $expectedLength   = chr(0x20);
        $expectedContent  = "\x7f\xff\xff\xff\xff\xff\xff\xff";
        $expectedContent .= "\xff\xff\xff\xff\xff\xff\xff\xff";
        $expectedContent .= "\xff\xff\xff\xff\xff\xff\xff\xff";
        $expectedContent .= "\xff\xff\xff\xff\xff\xff\xff\xff";

        // 2 ^ 255 - 1
        $bigint = '57896044618658097711785492504343953926634992332820282019728792003956564819967';
        $object = new Integer($bigint);
        $binary = $object->getBinary();
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $binary);

        $obj = ASNObject::fromBinary($binary);
        $this->assertEquals($obj, $object);

        // Test a bigint with a 1 in the most significant byte
        $expectedLength   = chr(0x21);
        $expectedContent  = "\x00\x80\x00\x00\x00\x00\x00\x00\x00";
        $expectedContent .= "\x00\x00\x00\x00\x00\x00\x00\x00";
        $expectedContent .= "\x00\x00\x00\x00\x00\x00\x00\x00";
        $expectedContent .= "\x00\x00\x00\x00\x00\x00\x00\x00";

        // 2 ^ 255
        $bigint = '57896044618658097711785492504343953926634992332820282019728792003956564819968';
        $object = new Integer($bigint);
        $binary = $object->getBinary();
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $binary);

        $obj = ASNObject::fromBinary($binary);
        $this->assertEquals($object, $obj);

        // Test a negative bigint
        $expectedType     = chr(Identifier::INTEGER);
        $expectedLength   = chr(0x20);
        $expectedContent  = "\x80\x00\x00\x00\x00\x00\x00\x00";
        $expectedContent .= "\x00\x00\x00\x00\x00\x00\x00\x00";
        $expectedContent .= "\x00\x00\x00\x00\x00\x00\x00\x00";
        $expectedContent .= "\x00\x00\x00\x00\x00\x00\x00\x01";

        // -(2 ^ 255 - 1)
        $bigint = '-57896044618658097711785492504343953926634992332820282019728792003956564819967';
        $object = new Integer($bigint);
        $binary = $object->getBinary();
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $binary);

        $obj = ASNObject::fromBinary($binary);
        $this->assertEquals($obj, $object);
    }

    /**
     * @dataProvider bigIntegersProvider
     */
    public function testSerializeBigIntegers($i)
    {
        $object = new Integer($i);
        $binary = $object->getBinary();

        $obj = ASNObject::fromBinary($binary);
        $this->assertEquals($obj->getContent(), $object->getContent());
    }

    public function bigIntegersProvider()
    {
        for ($i = 1; $i <= 256; $i *= 2) {
            // 2 ^ n [0, 256]  large positive numbers
            yield [(string)BigInteger::create(2)->toPower($i)];
        }

        for ($i = 1; $i <= 256; $i *= 2) {
            // 0 - 2 ^ n [0, 256]  large negative numbers
            yield [(string)BigInteger::create(0)->subtract(BigInteger::create(2)->toPower($i))];
        }
    }

    /**
     * @depends testGetBinary
     */
    public function testFromBinary()
    {
        $originalObject = new Integer(200);
        $binaryData = $originalObject->getBinary();
        $parsedObject = Integer::fromBinary($binaryData);
        $this->assertEquals($originalObject, $parsedObject);

        $originalObject = new Integer(12345);
        $binaryData = $originalObject->getBinary();
        $parsedObject = Integer::fromBinary($binaryData);
        $this->assertEquals($originalObject, $parsedObject);

        $originalObject = new Integer(-1891004);
        $binaryData = $originalObject->getBinary();
        $parsedObject = Integer::fromBinary($binaryData);
        $this->assertEquals($originalObject, $parsedObject);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithOffset()
    {
        $originalObject1 = new Integer(12345);
        $originalObject2 = new Integer(67890);

        $binaryData  = $originalObject1->getBinary();
        $binaryData .= $originalObject2->getBinary();

        $offset = 0;
        $parsedObject = Integer::fromBinary($binaryData, $offset);
        $this->assertEquals($originalObject1, $parsedObject);
        $this->assertEquals(4, $offset);
        $parsedObject = Integer::fromBinary($binaryData, $offset);
        $this->assertEquals($originalObject2, $parsedObject);
        $this->assertEquals(9, $offset);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithInvalidLength00()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 2: A FG\\ASN1\\Universal\\Integer should have a content length of at least 1. Extracted length was 0");

        $binaryData  = chr(Identifier::INTEGER);
        $binaryData .= chr(0x00);
        Integer::fromBinary($binaryData);
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithInvalidLength01()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 2: A FG\\ASN1\\Universal\\Integer should have a content length of at least 1. Extracted length was 0");

        $binaryData  = chr(Identifier::INTEGER);
        $binaryData .= chr(0x00);
        $binaryData .= chr(0xA0);
        Integer::fromBinary($binaryData);
    }

    public function getNearLimitsFixtures()
    {
        return [
            [0,"020100"],
            [127,"02017f"],
            [128,"02020080"],
            [256,"02020100"],
            [-128,"020180"],
            [-129,"0202ff7f"],
        ];
    }

    /**
     * @dataProvider getNearLimitsFixtures
     * @param $integerValue
     * @param $der
     * @throws \FG\ASN1\Exception\ParserException
     */
    public function testIntegerNearLimits($integerValue, $der)
    {
        $integer = new Integer($integerValue);
        $this->assertEquals($der, bin2hex($integer->getBinary()));

        $bin = hex2bin($der);
        $parsed = Integer::fromBinary($bin);
        $this->assertEquals($parsed->getType(), $integer->getType());
        $this->assertEquals($parsed->getContent(), $integer->getContent());
        $this->assertEquals($parsed->getObjectLength(), $integer->getObjectLength());
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithUnnecessary00Byte()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Integer not minimally encoded");

        $binaryData  = chr(Identifier::INTEGER);
        $binaryData .= chr(0x02);
        $binaryData .= chr(0x00);
        $binaryData .= chr(0x01);
        Integer::fromBinary($binaryData); // 02020001 should be encoded as 020101
    }

    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithUnnecessaryFFByte()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("Integer not minimally encoded");

        $binaryData  = chr(Identifier::INTEGER);
        $binaryData .= chr(0x02);
        $binaryData .= chr(0xFF);
        $binaryData .= chr(0xFF);
        Integer::fromBinary($binaryData);
    }

    /**
     * @depends testFromBinary
     */
    public function testRejectsNonMinimalEncodingExtraZero()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 2: Integer not minimally encoded");

        $binaryData  = chr(Identifier::INTEGER);
        $binaryData .= chr(0x02);
        $binaryData .= chr(0x00);
        $binaryData .= chr(0x01);
        Integer::fromBinary($binaryData);
    }

    /**
     * @depends testFromBinary
     */
    public function testRejectsNonMinimalEncodingExtraFF()
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage("ASN.1 Parser Exception at offset 2: Integer not minimally encoded");

        $binaryData  = chr(Identifier::INTEGER);
        $binaryData .= chr(0x02);
        $binaryData .= chr(0xff);
        $binaryData .= chr(0x80);
        Integer::fromBinary($binaryData);
    }
}
