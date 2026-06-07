<?php

declare(strict_types=1);

namespace Html2img\Enum;

/**
 * The API endpoints exposed by the client.
 *
 * Paths are relative to the configured base URI and already include the
 * `/api` prefix used by html2img.com.
 */
enum Endpoint: string
{
    case Html = '/api/html';
    case Screenshot = '/api/screenshot';
    case Template = '/api/v1/templates';

    /**
     * Build the request path for this endpoint.
     *
     * The template endpoint requires a slug, which is appended to the base
     * template path. The other endpoints ignore the slug.
     */
    public function path(?string $slug = null): string
    {
        if ($this === self::Template) {
            return $this->value.'/'.rawurlencode($slug ?? '');
        }

        return $this->value;
    }
}
