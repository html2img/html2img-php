<?php

declare(strict_types=1);

namespace Html2img\Exception;

use RuntimeException;
use Throwable;

/**
 * Base type for every exception thrown by the client.
 *
 * Catching this single type is enough to handle any failure originating
 * from the SDK. No raw Guzzle exception is ever allowed to escape the
 * public API.
 */
class Html2imgException extends RuntimeException
{
    /**
     * @param  string  $message  Human-readable error message.
     * @param  int|null  $statusCode  The HTTP status code, when the failure came from a response.
     * @param  array<string, mixed>  $payload  The decoded JSON response body, when available.
     * @param  string|null  $errorCode  The machine-readable `code` field from the API body, when present.
     */
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        public readonly array $payload = [],
        public readonly ?string $errorCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * The HTTP status code associated with this failure, if any.
     */
    public function statusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * The machine-readable `code` from the API response body, if any.
     */
    public function errorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * The decoded JSON response body, if any.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
