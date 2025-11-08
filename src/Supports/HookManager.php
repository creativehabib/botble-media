<?php

namespace Botble\Media\Supports;

use Closure;
use Illuminate\Contracts\Container\Container;

class HookManager
{
    protected array $filters = [];

    protected array $actions = [];

    public function __construct(protected Container $container)
    {
    }

    public function addFilter(string $hook, Closure $callback, int $priority = 10): void
    {
        $this->filters[$hook][$priority][] = $callback;
    }

    public function applyFilters(string $hook, $value, ...$arguments)
    {
        if (! isset($this->filters[$hook])) {
            return $value;
        }

        ksort($this->filters[$hook]);

        foreach ($this->filters[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $value = $this->container->call($callback, array_merge([$value], $arguments));
            }
        }

        return $value;
    }

    public function addAction(string $hook, Closure $callback, int $priority = 10): void
    {
        $this->actions[$hook][$priority][] = $callback;
    }

    public function dispatchAction(string $hook, ...$arguments): void
    {
        if (! isset($this->actions[$hook])) {
            return;
        }

        ksort($this->actions[$hook]);

        foreach ($this->actions[$hook] as $callbacks) {
            foreach ($callbacks as $callback) {
                $this->container->call($callback, $arguments);
            }
        }
    }
}
