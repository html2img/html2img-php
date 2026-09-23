<?php

declare(strict_types=1);

namespace Html2img\Exception;

/**
 * Thrown on a 402 response: the account is authenticated but out of credits.
 * Free accounts have a one-off allowance of 50 credits; paid plans renew each
 * billing period. The API `code` is `insufficient_credits`.
 */
final class InsufficientCreditsException extends Html2imgException
{
    /**
     * Credits remaining on the account, as reported by the API. Zero on a
     * 402 response.
     */
    public function creditsRemaining(): ?int
    {
        $value = $this->payload['credits_remaining'] ?? null;

        return is_int($value) ? $value : null;
    }
}
