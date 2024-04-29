<?php
/*
 * This file is part of the PHPASN1 library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace FG\Utility;

/**
 * Class BigInteger
 * Utility class to remove dependence on a single large number library. Not intended for external use, this class only
 * implements the functionality needed throughout this project.
 *
 * Instances are immutable, all operations return a new instance with the result.
 *
 * @package FG\Utility
 * @internal
 */
abstract class BigInteger
{
    /**
     * Force a preference on the underlying big number implementation, useful for testing.
     */
    private static string|int|null $_prefer = null;

    public static function setPrefer(string|int $prefer = null)
    {
        self::$_prefer = $prefer;
    }

    /**
     * Create a BigInteger instance based off the base 10 string or an integer.
     * @throws \InvalidArgumentException
     */
    public static function create(string|int $val): BigInteger
    {
        if (self::$_prefer) {
            $ret = match (self::$_prefer) {
                'gmp' => new BigIntegerGmp(),
                'bcmath' => new BigIntegerBcmath(),
                default => throw new \UnexpectedValueException('Unknown number implementation: ' . self::$_prefer),
            };
        }
        else {
            // autodetect
            if (function_exists('gmp_add')) {
                $ret = new BigIntegerGmp();
            }
            elseif (function_exists('bcadd')) {
                $ret = new BigIntegerBcmath();
            } else {
                throw new \RuntimeException('Requires GMP or bcmath extension.');
            }
        }

        if (is_int($val)) {
            $ret->_fromInteger($val);
        }
        else {
            // convert to string, if not already one
            $val = (string)$val;

            // validate string
            if (!preg_match('/^-?[0-9]+$/', $val)) {
                throw new \InvalidArgumentException('Expects a string representation of an integer.');
            }
            $ret->_fromString($val);
        }

        return $ret;
    }

    /**
     * BigInteger constructor.
     * Prevent directly instantiating object, use BigInteger::create instead.
     */
    protected function __construct()
    {

    }

    /**
     * Subclasses must provide clone functionality.
     */
    abstract public function __clone(): void;

    /**
     * Assign the instance value from base 10 string.
     */
    abstract protected function _fromString(string $str): void;

    /**
     * Assign the instance value from an integer type.
     */
    abstract protected function _fromInteger(int $integer): void;

    /**
     * Must provide string implementation that returns base 10 number.
     */
    abstract public function __toString(): string;

    /* INFORMATIONAL FUNCTIONS */

    /**
     * Return integer, if possible. Throws an exception if the number can not be represented as a native integer.
     * @throws \OverflowException
     */
    abstract public function toInteger(): int;

    /**
     * Is represented integer negative?
     */
    abstract public function isNegative(): bool;

    /**
     * Compare the integer with $number, returns a negative integer if $this is less than number, returns 0 if $this is
     * equal to number and returns a positive integer if $this is greater than number.
     */
    abstract public function compare(BigInteger|string|int $number): int;

    /**
     * Add another integer $b and returns the result.
     */
    abstract public function add(BigInteger|string|int $b): BigInteger;

    /**
     * Subtract $b from $this and returns the result.
     */
    abstract public function subtract(BigInteger|string|int $b): BigInteger;

    /**
     * Multiply value.
     */
    abstract public function multiply(BigInteger|string|int $b): BigInteger;

    /**
     * The value $this modulus $b.
     */
    abstract public function modulus(BigInteger|string|int $b): BigInteger;

    /**
     * Raise $this to the power of $b and returns the result.
     */
    abstract public function toPower(BigInteger|string|int $b): BigInteger;

    /**
     * Shift the value to the right by a set number of bits and returns the result.
     */
    abstract public function shiftRight(int $bits = 8): BigInteger;

    /**
     * Shift the value to the left by a set number of bits and returns the result.
     */
    abstract public function shiftLeft(int $bits = 8): BigInteger;

    /**
     * Returns the absolute value.
     */
    abstract public function absoluteValue(): BigInteger;
}
