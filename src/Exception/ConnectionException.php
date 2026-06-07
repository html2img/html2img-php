<?php

declare(strict_types=1);

namespace Html2img\Exception;

/**
 * Thrown when the request never reaches a response: DNS failure, refused
 * connection, TLS error or a transport-level timeout. Wraps Guzzle's
 * `ConnectException` and any other transfer error with no usable response.
 */
final class ConnectionException extends Html2imgException {}
