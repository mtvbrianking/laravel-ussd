<?php

namespace Bmatovu\Ussd\Tests\Support;

use Bmatovu\Ussd\Support\Util;
use Bmatovu\Ussd\Tests\TestCase;

class UtilTest extends TestCase
{
    public function testNumberComparisons()
    {
        static::assertTrue(Util::compare('16', 'lt', '18'));
        static::assertFalse(Util::compare('18', 'lt', '18'));
        static::assertFalse(Util::compare('20', 'lt', '18'));

        static::assertFalse(Util::compare('16', 'gt', '18'));
        static::assertFalse(Util::compare('18', 'gt', '18'));
        static::asserttrue(Util::compare('20', 'gt', '18'));

        static::assertTrue(Util::compare('16', 'lte', '18'));
        static::assertTrue(Util::compare('18', 'lte', '18'));
        static::assertFalse(Util::compare('20', 'lte', '18'));

        static::assertFalse(Util::compare('16', 'gte', '18'));
        static::assertTrue(Util::compare('18', 'gte', '18'));
        static::assertTrue(Util::compare('20', 'gte', '18'));

        static::assertFalse(Util::compare('16', 'eq', '18'));
        static::assertTrue(Util::compare('18', 'eq', '18'));
        static::assertFalse(Util::compare('20', 'eq', '18'));

        static::assertFalse(Util::compare('18', 'unknown', '18'));

        static::assertTrue(Util::compare('1', 'ne', '1.1'));
        static::assertFalse(Util::compare('1', 'ne', '1.0'));

        static::assertTrue(Util::compare('3', 'btn', '1,5'));
        static::assertFalse(Util::compare('7', 'btn', '1,5'));
    }

    public function testStringComparisons()
    {
        static::assertTrue(Util::compare('foo', 'str.equals', 'foo'));
        static::assertFalse(Util::compare('bar', 'str.equals', 'foo'));

        static::assertFalse(Util::compare('foo', 'str.not_equals', 'foo'));
        static::assertTrue(Util::compare('bar', 'str.not_equals', 'foo'));

        static::assertTrue(Util::compare('prefix.other', 'str.starts', 'prefix'));
        static::assertFalse(Util::compare('other', 'str.starts', 'prefix'));

        static::assertTrue(Util::compare('other.suffix', 'str.ends', 'suffix'));
        static::assertFalse(Util::compare('other', 'str.ends', 'suffix'));

        static::assertTrue(Util::compare('test sub strings', 'str.contains', 'sub'));
        static::assertFalse(Util::compare('test strings', 'str.contains', 'sub'));
    }

    public function testRegexComparisons()
    {
        static::assertTrue(Util::compare('12345', 'regex.match', '/^[0-9]{5}$/'));
        static::assertFalse(Util::compare('123456789', 'regex.match', '/^[0-9]{5}$/'));
    }

    public function testArrayComparisons()
    {
        static::assertTrue(Util::compare('foo', 'arr.in', 'foo,bar'));
        static::assertFalse(Util::compare('foo', 'arr.in', ''));

        static::assertTrue(Util::compare('foo', 'arr.not_in', ''));
        static::assertFalse(Util::compare('foo', 'arr.not_in', 'foo,bar'));
    }

    public function testDateComparisons()
    {
        static::assertTrue(Util::compare('1990-05-12', 'date.equals', '1990-05-12'));
        static::assertFalse(Util::compare('2020-05-12', 'date.equals', '1990-05-12'));

        static::assertTrue(Util::compare('1980-05-12', 'date.before', '1990-05-12'));
        static::assertFalse(Util::compare('2020-05-12', 'date.before', '1990-05-12'));

        static::assertFalse(Util::compare('1980-05-12', 'date.after', '1990-05-12'));
        static::assertTrue(Util::compare('2020-05-12', 'date.after', '1990-05-12'));

        static::assertTrue(Util::compare('1990-05-12', 'date.between', '1980-05-12,2000-05-12'));
        static::assertFalse(Util::compare('2020-05-12', 'date.between', '1980-05-12,2000-05-12'));
    }

    public function testTimeComparisons()
    {
        static::assertTrue(Util::compare('14:00:00', 'time.equals', '14:00:00'));
        static::assertFalse(Util::compare('17:00:00', 'time.equals', '14:00:00'));

        static::assertTrue(Util::compare('12:00:00', 'time.before', '14:00:00'));
        static::assertFalse(Util::compare('17:00:00', 'time.before', '14:00:00'));

        static::assertFalse(Util::compare('12:00:00', 'time.after', '14:00:00'));
        static::assertTrue(Util::compare('17:00:00', 'time.after', '14:00:00'));

        static::assertTrue(Util::compare('14:00:00', 'time.between', '12:00:00,15:00:00'));
        static::assertFalse(Util::compare('17:00:00', 'time.between', '12:00:00,15:00:00'));
    }
}
