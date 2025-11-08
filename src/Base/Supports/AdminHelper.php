<?php

namespace Botble\Base\Supports;

use Closure;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Http\Request;

class AdminHelper
{
    public function __construct(protected Registrar $router, protected Request $request)
    {
    }

    public function registerRoutes(Closure $routes): void
    {
        $routes();
    }

    public function isInAdmin(bool $force = false): bool
    {
        if ($force) {
            return $this->request->is($this->adminPrefix() . '*');
        }

        $route = $this->request->route();

        if (! $route) {
            return false;
        }

        $prefix = trim((string) $route->getPrefix(), '/');

        return $prefix === $this->adminPrefix();
    }

    protected function adminPrefix(): string
    {
        return trim(config('core.media.media.route.prefix', config('media.route.prefix', 'media')), '/');
    }
}
