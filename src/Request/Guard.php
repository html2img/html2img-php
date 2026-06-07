<?php

declare(strict_types=1);

namespace Html2img\Request;

use InvalidArgumentException;

/**
 * Internal range checks shared by the request objects.
 *
 * These mirror the server-side validation rules so obvious mistakes fail
 * fast with a clear message, before a request is sent.
 *
 * @internal
 */
final class Guard
{
    /**
     * Validate a viewport dimension (width or height): 1 to 5000 pixels.
     */
    public static function dimension(string $name, ?int $value): void
    {
        if ($value !== null && ($value < 1 || $value > 5000)) {
            throw new InvalidArgumentException("The {$name} must be between 1 and 5000.");
        }
    }

    /**
     * Validate the device pixel ratio: 1 to 4.
     */
    public static function dpi(?int $value): void
    {
        if ($value !== null && ($value < 1 || $value > 4)) {
            throw new InvalidArgumentException('The dpi must be between 1 and 4.');
        }
    }

    /**
     * Validate the post-load delay: 1 to 5000 milliseconds.
     */
    public static function msDelay(?int $value): void
    {
        if ($value !== null && ($value < 1 || $value > 5000)) {
            throw new InvalidArgumentException('The ms_delay must be between 1 and 5000.');
        }
    }
}
