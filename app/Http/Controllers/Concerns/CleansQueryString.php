<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Reads a query string parameter as a trimmed, length-bounded string.
 *
 * Public and admin listings accept arbitrary query strings, so a malformed
 * value (?q[]=x, an object, a very long string) must degrade to "no filter"
 * instead of raising a type error or hammering the database.
 */
trait CleansQueryString
{
    protected function queryString(Request $request, string $key, int $maxLength = 100): ?string
    {
        $value = $request->query($key);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : Str::limit($value, $maxLength, '');
    }
}
