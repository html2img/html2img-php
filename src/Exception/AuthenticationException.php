<?php

declare(strict_types=1);

namespace Html2img\Exception;

/**
 * Thrown on a 401 response: the API key is missing or not recognised.
 *
 * The API `code` is `missing_api_key` or `invalid_api_key`.
 */
final class AuthenticationException extends Html2imgException {}
