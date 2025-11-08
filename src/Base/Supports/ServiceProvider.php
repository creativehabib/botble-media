<?php

namespace Botble\Base\Supports;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

abstract class ServiceProvider extends IlluminateServiceProvider
{
    use LoadAndPublishDataTrait;
}
