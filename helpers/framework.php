<?php

use Botble\Media\Supports\HookManager;
use Botble\Setting\Supports\SettingStore;

if (! function_exists('apply_filters')) {
    function apply_filters(string $hook, $value, ...$arguments)
    {
        return app(HookManager::class)->applyFilters($hook, $value, ...$arguments);
    }
}

if (! function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10): void
    {
        app(HookManager::class)->addFilter($hook, \Closure::fromCallable($callback), $priority);
    }
}

if (! function_exists('do_action')) {
    function do_action(string $hook, ...$arguments): void
    {
        app(HookManager::class)->dispatchAction($hook, ...$arguments);
    }
}

if (! function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10): void
    {
        app(HookManager::class)->addAction($hook, \Closure::fromCallable($callback), $priority);
    }
}

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        $app = app();

        if ($app->bound(SettingStore::class)) {
            return $app->make(SettingStore::class)->get($key, $default);
        }

        if (function_exists('config')) {
            $config = config();

            return $config->get("core.media.media.settings.{$key}", $config->get("media.settings.{$key}", $default));
        }

        return $default;
    }
}
