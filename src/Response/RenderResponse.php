<?php

declare(strict_types=1);

namespace Html2img\Response;

/**
 * The result of a successful render call.
 *
 * Covers both the synchronous envelope (`url` populated) and the
 * asynchronous acceptance envelope returned when a `webhook_url` was
 * supplied (`status` is `processing` and `url` is null until the webhook
 * fires).
 */
final readonly class RenderResponse
{
    /**
     * @param  array<string, mixed>  $raw  The full decoded JSON payload.
     */
    public function __construct(
        public bool $success,
        public ?string $id,
        public ?string $url,
        public ?int $creditsRemaining,
        public ?string $status,
        public ?string $message,
        public ?string $template,
        public array $raw,
    ) {}

    /**
     * Build a response from a decoded JSON payload.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            success: (bool) ($data['success'] ?? false),
            id: isset($data['id']) ? (string) $data['id'] : null,
            url: isset($data['url']) ? (string) $data['url'] : null,
            creditsRemaining: array_key_exists('credits_remaining', $data) && is_numeric($data['credits_remaining'])
                ? (int) $data['credits_remaining']
                : null,
            status: isset($data['status']) ? (string) $data['status'] : null,
            message: isset($data['message']) ? (string) $data['message'] : null,
            template: isset($data['template']) ? (string) $data['template'] : null,
            raw: $data,
        );
    }

    /**
     * Whether this is an async job still being rendered.
     *
     * When true, the final image URL is delivered to the request's
     * `webhook_url` rather than being available on this response.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * The full decoded JSON payload, for access to any field not surfaced
     * as a typed property.
     *
     * @return array<string, mixed>
     */
    public function raw(): array
    {
        return $this->raw;
    }
}
