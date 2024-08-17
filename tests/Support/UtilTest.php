<?php

namespace Bmatovu\Ussd\Tests\Support;

use Bmatovu\Ussd\Support\Util;
use Bmatovu\Ussd\Tests\TestCase;

class UtilTest extends TestCase
{
    public function testNumberComparisons()
    {
        self::assertTrue(Util::compare('16', 'lt', '18'));
        self::assertFalse(Util::compare('18', 'lt', '18'));
        self::assertFalse(Util::compare('20', 'lt', '18'));

        self::assertFalse(Util::compare('16', 'gt', '18'));
        self::assertFalse(Util::compare('18', 'gt', '18'));
        static::asserttrue(Util::compare('20', 'gt', '18'));

        self::assertTrue(Util::compare('16', 'lte', '18'));
        self::assertTrue(Util::compare('18', 'lte', '18'));
        self::assertFalse(Util::compare('20', 'lte', '18'));

        self::assertFalse(Util::compare('16', 'gte', '18'));
        self::assertTrue(Util::compare('18', 'gte', '18'));
        self::assertTrue(Util::compare('20', 'gte', '18'));

        self::assertFalse(Util::compare('16', 'eq', '18'));
        self::assertTrue(Util::compare('18', 'eq', '18'));
        self::assertFalse(Util::compare('20', 'eq', '18'));

        // Defaults...
        self::assertTrue(Util::compare('18', '', '18'));
        self::assertTrue(Util::compare('18', 'unknown', '18'));

        self::assertTrue(Util::compare('1', 'ne', '1.1'));
        // self::assertTrue(Util::compare('1', 'ne', '1.0'));

        self::assertTrue(Util::compare('3', 'btn', '1,5'));
        self::assertFalse(Util::compare('7', 'btn', '1,5'));
    }

    public function testStringComparisons()
    {
        self::assertTrue(Util::compare('foo', 'str.equals', 'foo'));
        self::assertFalse(Util::compare('bar', 'str.equals', 'foo'));

        self::assertFalse(Util::compare('foo', 'str.not_equals', 'foo'));
        self::assertTrue(Util::compare('bar', 'str.not_equals', 'foo'));

        self::assertTrue(Util::compare('prefix.other', 'str.starts', 'prefix'));
        self::assertFalse(Util::compare('other', 'str.starts', 'prefix'));

        self::assertTrue(Util::compare('other.suffix', 'str.ends', 'suffix'));
        self::assertFalse(Util::compare('other', 'str.ends', 'suffix'));

        self::assertTrue(Util::compare('test sub strings', 'str.contains', 'sub'));
        self::assertFalse(Util::compare('test strings', 'str.contains', 'sub'));
    }

    public function testRegexComparisons()
    {
        self::assertTrue(Util::compare('12345', 'regex.match', '/^[0-9]{5}$/'));
        self::assertFalse(Util::compare('123456789', 'regex.match', '/^[0-9]{5}$/'));
    }

    public function testArrayComparisons()
    {
        self::assertTrue(Util::compare('foo', 'arr.in', 'foo,bar'));
        self::assertFalse(Util::compare('foo', 'arr.in', ''));

        self::assertTrue(Util::compare('foo', 'arr.not_in', ''));
        self::assertFalse(Util::compare('foo', 'arr.not_in', 'foo,bar'));
    }

    public function testDateComparisons()
    {
        self::assertTrue(Util::compare('1990-05-12', 'date.equals', '1990-05-12'));
        self::assertFalse(Util::compare('2020-05-12', 'date.equals', '1990-05-12'));

        self::assertTrue(Util::compare('1980-05-12', 'date.before', '1990-05-12'));
        self::assertFalse(Util::compare('2020-05-12', 'date.before', '1990-05-12'));

        self::assertFalse(Util::compare('1980-05-12', 'date.after', '1990-05-12'));
        self::assertTrue(Util::compare('2020-05-12', 'date.after', '1990-05-12'));

        self::assertTrue(Util::compare('1990-05-12', 'date.between', '1980-05-12,2000-05-12'));
        self::assertFalse(Util::compare('2020-05-12', 'date.between', '1980-05-12,2000-05-12'));
    }

    public function testTimeComparisons()
    {
        self::assertTrue(Util::compare('14:00:00', 'time.equals', '14:00:00'));
        self::assertFalse(Util::compare('17:00:00', 'time.equals', '14:00:00'));

        self::assertTrue(Util::compare('12:00:00', 'time.before', '14:00:00'));
        self::assertFalse(Util::compare('17:00:00', 'time.before', '14:00:00'));

        self::assertFalse(Util::compare('12:00:00', 'time.after', '14:00:00'));
        self::assertTrue(Util::compare('17:00:00', 'time.after', '14:00:00'));

        self::assertTrue(Util::compare('14:00:00', 'time.between', '12:00:00,15:00:00'));
        self::assertFalse(Util::compare('17:00:00', 'time.between', '12:00:00,15:00:00'));
    }
}
