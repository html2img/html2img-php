<?php

declare(strict_types=1);

namespace Html2img\Exception;

/**
 * Thrown on a 403 response: the key is valid but the account has no active
 * subscription. The API `code` is `not_subscribed`.
 */
final class NotSubscribedException extends Html2imgException {}
