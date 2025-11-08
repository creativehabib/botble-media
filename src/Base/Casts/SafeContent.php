<?php

namespace Botble\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;

class SafeContent implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        return Str::of($value)->stripTags()->__toString();
    }
}
