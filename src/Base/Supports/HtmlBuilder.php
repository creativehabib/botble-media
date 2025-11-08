<?php

namespace Botble\Base\Supports;

class HtmlBuilder
{
    public function tag(string $tag, string $content = '', array $attributes = []): string
    {
        $attributeString = $this->attributes($attributes);

        return sprintf('<%s%s>%s</%s>', $tag, $attributeString, $content, $tag);
    }

    public function image(string $url, ?string $alt = null, array $attributes = [], ?bool $secure = null): string
    {
        $attributes = array_merge(['src' => $this->formatUrl($url, $secure), 'alt' => $alt], $attributes);

        return $this->tag('img', '', $attributes);
    }

    protected function attributes(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        $compiled = collect($attributes)
            ->reject(fn ($value) => $value === null)
            ->map(fn ($value, $key) => sprintf('%s="%s"', $key, e($value)))
            ->implode(' ');

        return ' ' . $compiled;
    }

    protected function formatUrl(string $url, ?bool $secure = null): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return app('url')->asset($url, $secure);
    }
}
