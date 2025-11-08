<?php

namespace Botble\Base\Traits;

trait LoadAndPublishDataTrait
{
    protected string $moduleNamespace = '';

    protected function modulePath(string $path = ''): string
    {
        $basePath = realpath(__DIR__ . '/../../..');

        return rtrim($basePath . ($path ? DIRECTORY_SEPARATOR . $path : ''), DIRECTORY_SEPARATOR);
    }

    public function setNamespace(string $namespace): static
    {
        $this->moduleNamespace = $namespace;

        return $this;
    }

    protected function loadHelpers(): static
    {
        $helpers = glob($this->modulePath('helpers') . DIRECTORY_SEPARATOR . '*.php');

        foreach ($helpers as $helper) {
            require_once $helper;
        }

        return $this;
    }

    protected function loadAndPublishConfigurations(array $files): static
    {
        foreach ($files as $file) {
            $path = $this->modulePath('config' . DIRECTORY_SEPARATOR . $file . '.php');

            $key = str_replace('/', '.', $this->moduleNamespace) . '.' . $file;

            $this->mergeConfigFrom($path, $key);

            $this->publishes([
                $path => config_path(str_replace('/', DIRECTORY_SEPARATOR, $this->moduleNamespace) . DIRECTORY_SEPARATOR . $file . '.php'),
            ], 'botble-media-config');
        }

        return $this;
    }

    protected function loadAndPublishTranslations(): static
    {
        $path = $this->modulePath('resources' . DIRECTORY_SEPARATOR . 'lang');

        $this->loadTranslationsFrom($path, $this->moduleNamespace);

        $this->publishes([$path => lang_path('vendor' . DIRECTORY_SEPARATOR . $this->moduleNamespace)], 'botble-media-translations');

        return $this;
    }

    protected function loadAndPublishViews(): static
    {
        $path = $this->modulePath('resources' . DIRECTORY_SEPARATOR . 'views');

        $this->loadViewsFrom($path, $this->moduleNamespace);

        $this->publishes([$path => resource_path('views/vendor/' . $this->moduleNamespace)], 'botble-media-views');

        return $this;
    }

    protected function loadRoutes(): static
    {
        $path = $this->modulePath('routes' . DIRECTORY_SEPARATOR . 'web.php');

        if (file_exists($path)) {
            $this->loadRoutesFrom($path);
        }

        return $this;
    }

    protected function loadMigrations(): static
    {
        $path = $this->modulePath('database' . DIRECTORY_SEPARATOR . 'migrations');

        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }

        return $this;
    }

    protected function publishAssets(): static
    {
        $path = $this->modulePath('public');

        if (is_dir($path)) {
            $this->publishes([
                $path => public_path('vendor/botble/media'),
            ], 'botble-media-assets');
        }

        return $this;
    }
}
