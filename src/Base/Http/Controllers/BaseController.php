<?php

namespace Botble\Base\Http\Controllers;

use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Support\Facades\View;

abstract class BaseController extends LaravelController
{
    protected function pageTitle(string $title, ?string $subTitle = null): static
    {
        View::share('pageTitle', $title);

        if ($subTitle !== null) {
            View::share('pageSubTitle', $subTitle);
        }

        return $this;
    }
}
