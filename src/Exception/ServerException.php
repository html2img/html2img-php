<?php

declare(strict_types=1);

namespace Html2img\Exception;

/**
 * Thrown on a 5xx response when the renderer returns an unexpected error.
 * The API `code` is `service_error`. Retry once; if it persists, contact
 * support with the `id` from the payload.
 */
final class ServerException extends Html2imgException {}
