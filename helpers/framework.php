<?php

use Botble\Media\Supports\HookManager;
use Closure;

if (! function_exists('apply_filters')) {
    function apply_filters(string $hook, $value, ...$arguments)
    {
        return app(HookManager::class)->applyFilters($hook, $value, ...$arguments);
    }
}

if (! function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10): void
    {
        app(HookManager::class)->addFilter($hook, Closure::fromCallable($callback), $priority);
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
        app(HookManager::class)->addAction($hook, Closure::fromCallable($callback), $priority);
    }
}
