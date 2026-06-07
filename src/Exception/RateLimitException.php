<?php

declare(strict_types=1);

namespace Html2img\Exception;

/**
 * Thrown on a 429 response when the account exceeds its request rate or
 * plan quota.
 */
final class RateLimitException extends Html2imgException {}
