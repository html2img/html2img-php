<?php

declare(strict_types=1);

namespace Html2img\Exception;

use Throwable;

/**
 * Thrown on a 400 or 422 response when one or more request fields fail
 * validation. The API `code` is `validation_error`.
 */
final class ValidationException extends Html2imgException
{
    /**
     * @param  array<string, list<string>>  $details  Map of field name to its validation messages.
     * @param  array<string, mixed>  $payload  The decoded JSON response body.
     */
    public function __construct(
        string $message,
        public readonly array $details = [],
        ?int $statusCode = null,
        array $payload = [],
        ?string $errorCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $payload, $errorCode, $previous);
    }

    /**
     * The per-field validation messages returned by the API.
     *
     * @return array<string, list<string>>
     */
    public function details(): array
    {
        return $this->details;
    }
}
