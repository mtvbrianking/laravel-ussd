<?php

namespace Bmatovu\Ussd\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;

class Util
{
    public static function regex($pattern): string
    {
        return Str::startsWith($pattern, '/') ? $pattern : "/{$pattern}/";
    }
    public static function compare($key, $condition, $value): bool
    {
        switch ($condition) {
                // -------------------- Numbers --------------------
            case 'lt':
                return $key < $value;
            case 'gt':
                return $key > $value;
            case 'lte':
                return $key <= $value;
            case 'gte':
                return $key >= $value;
            case 'eq':
                return $key == $value;
            case 'ne':
                return $key != $value;
            case 'btn':
                list($min, $max) = explode(',', $value);
                return ($min <= $key) && ($key <= $max);
                // -------------------- Strings --------------------
            case 'str.equals':
                return $key == $value;
            case 'str.not_equals':
                return $key != $value;
            case 'str.starts':
                return Str::startsWith($key, $value);
            case 'str.ends':
                return Str::endsWith($key, $value);
            case 'str.contains':
                $values = explode(',', $value);
                return Str::contains($key, $values);
                // -------------------- Regex --------------------
            case 'regex.match':
                return preg_match(static::regex($value), $key);
                // -------------------- Arrays --------------------
            case 'arr.in':
                $values = explode(',', $value);
                return in_array($key, $values);
            case 'arr.not_in':
                $values = explode(',', $value);
                return !in_array($key, $values);
                // -------------------- Dates --------------------
            case 'date.equals':
                // Format: Y-m-d
                $key = new Carbon($key);
                $value = new Carbon($value);
                return $key->toDateString() == $value->toDateString();
            case 'date.before':
                $key = new Carbon($key);
                $value = new Carbon($value);
                return $key->isBefore($value);
            case 'date.after':
                $key = new Carbon($key);
                $value = new Carbon($value);
                return $key->isAfter($value);
            case 'date.between':
                $key = new Carbon($key);

                list($start, $end) = explode(',', $value);
                $start = new Carbon($start);
                $end = new Carbon($end);
                return $key->between($start, $end);
                // -------------------- Time --------------------
            case 'time.equals':
                // Format: H:i:s
                $key = new Carbon($key);
                return $key->toTimeString('second') == $value;
            case 'time.before':
                $key = (new Carbon($key))->format('His');
                $value = now()->setTimeFromTimeString($value)->format('His');
                return $key < $value;
            case 'time.after':
                $key = (new Carbon($key))->format('His');
                $value = now()->setTimeFromTimeString($value)->format('His');
                return $key > $value;
            case 'time.between':
                $key = (new Carbon($key))->format('His');

                list($start, $end) = explode(',', $value);
                $min = now()->setTimeFromTimeString($start)->format('His');
                $max = now()->setTimeFromTimeString($end)->format('His');
                return ($min <= $key) && ($key <= $max);
            default:
                return false;
        }
    }
}
