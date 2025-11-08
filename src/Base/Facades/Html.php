<?php

namespace Botble\Base\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string tag(string $tag, string $content = '', array $attributes = [])
 * @method static string image(string $url, ?string $alt = null, array $attributes = [], ?bool $secure = null)
 */
class Html extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'BotbleMedia.base.html';
    }
}
