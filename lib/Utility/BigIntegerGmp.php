<?php
/*
 * This file is part of the PHPASN1 library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace FG\Utility;

use GMP;

/**
 * Class BigIntegerGmp
 * Integer representation of big numbers using the GMP extension to perform operations.
 * @package FG\Utility
 * @internal
 */
class BigIntegerGmp extends BigInteger
{
    protected GMP $_rh;

    public function __clone(): void
    {
        $this->_rh = gmp_add($this->_rh, 0);
    }

    protected function _fromString($str): void
    {
        $this->_rh = gmp_init($str, 10);
    }

    protected function _fromInteger($integer): void
    {
        $this->_rh = gmp_init($integer, 10);
    }

    public function __toString(): string
    {
        return gmp_strval($this->_rh, 10);
    }

    public function toInteger(): int
    {
        if ($this->compare(PHP_INT_MAX) > 0 || $this->compare(PHP_INT_MIN) < 0) {
            throw new \OverflowException(sprintf('Can not represent %s as integer.', $this));
        }
        return gmp_intval($this->_rh);
    }

    public function isNegative(): bool
    {
        return gmp_sign($this->_rh) === -1;
    }

    protected function _unwrap(BigInteger|string|int $number): BigInteger|GMP|string|int
    {
        if ($number instanceof self) {
            return $number->_rh;
        }
        return $number;
    }

    public function compare(BigInteger|string|int $number): int
    {
        return gmp_cmp($this->_rh, $this->_unwrap($number));
    }

    public function add(BigInteger|string|int $b): BigInteger
    {
        $ret = new self();
        $ret->_rh = gmp_add($this->_rh, $this->_unwrap($b));
        return $ret;
    }

    public function subtract(BigInteger|string|int $b): BigInteger
    {
        $ret = new self();
        $ret->_rh = gmp_sub($this->_rh, $this->_unwrap($b));
        return $ret;
    }

    public function multiply(BigInteger|string|int $b): BigInteger
    {
        $ret = new self();
        $ret->_rh = gmp_mul($this->_rh, $this->_unwrap($b));
        return $ret;
    }

    public function modulus(BigInteger|string|int $b): BigInteger
    {
        $ret = new self();
        $ret->_rh = gmp_mod($this->_rh, $this->_unwrap($b));
        return $ret;
    }

    public function toPower(BigInteger|string|int $b): BigInteger
    {
        if ($b instanceof self) {
            // gmp_pow accepts just an integer
            if ($b->compare(PHP_INT_MAX) > 0) {
                throw new \UnexpectedValueException('Unable to raise to power greater than PHP_INT_MAX.');
            }
            $b = gmp_intval($b->_rh);
        }
        $ret = new self();
        $ret->_rh = $this->_rh ** $b;
        return $ret;
    }

    public function shiftRight(int $bits = 8): BigInteger
    {
        $ret = new self();
        $ret->_rh = $this->_rh >> $bits;
        return $ret;
    }

    public function shiftLeft(int $bits = 8): BigInteger
    {
        $ret = new self();
        $ret->_rh = $this->_rh << $bits;
        return $ret;
    }

    public function absoluteValue(): BigInteger
    {
        $ret = new self();
        $ret->_rh = gmp_abs($this->_rh);
        return $ret;
    }
}
