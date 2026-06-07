<?php

declare(strict_types=1);

namespace Html2img\Exception;

/**
 * Thrown on a 504 response when a synchronous render exceeds the renderer
 * budget. The API `code` is `timeout_error` or `api_timeout_error`.
 *
 * For captures that routinely take this long, supply a `webhook_url` on the
 * request to switch to asynchronous delivery.
 */
final class TimeoutException extends Html2imgException {}
