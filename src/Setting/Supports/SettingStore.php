<?php

namespace Botble\Setting\Supports;

use Illuminate\Support\Arr;

class SettingStore
{
    protected array $dirty = [];

    public function __construct(protected array $settings = [])
    {
    }

    public function get(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function set(string $key, $value): static
    {
        data_set($this->settings, $key, $value);
        data_set($this->dirty, $key, $value);

        return $this;
    }

    public function forceSet(string $key, $value): static
    {
        return $this->set($key, $value);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->settings, $key);
    }

    public function forget(string $key): static
    {
        Arr::forget($this->settings, $key);
        Arr::forget($this->dirty, $key);

        return $this;
    }

    public function all(): array
    {
        return $this->settings;
    }

    public function save(): static
    {
        $this->dirty = [];

        return $this;
    }
}
