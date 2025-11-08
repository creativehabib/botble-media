<?php

namespace Botble\Base\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string humanFilesize(int|float $bytes, int $decimals = 2)
 * @method static void logError($exception)
 * @method static string|null clean(?string $value)
 * @method static string joinPaths(array $segments)
 * @method static string renderIcon(string $class, array $attributes = [])
 * @method static void maximumExecutionTimeAndMemoryLimit()
 * @method static string|null formatDate($date, string $format = 'Y-m-d H:i:s')
 */
class BaseHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'BotbleMedia.base.helper';
    }
}
