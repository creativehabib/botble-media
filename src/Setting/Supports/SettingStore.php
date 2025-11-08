<?php

namespace Botble\Setting\Supports;

class SettingStore
{
    public function __construct(protected array $settings = [])
    {
    }

    public function get(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }
}
