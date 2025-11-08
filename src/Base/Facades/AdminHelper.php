<?php

namespace Botble\Base\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void registerRoutes(\Closure $routes)
 * @method static bool isInAdmin(bool $force = false)
 */
class AdminHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'BotbleMedia.base.admin-helper';
    }
}
