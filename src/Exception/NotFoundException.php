<?php

declare(strict_types=1);

namespace Html2img\Exception;

/**
 * Thrown on a 404 response, for example when a template slug does not exist.
 * The API `code` is `template_not_found`.
 */
final class NotFoundException extends Html2imgException {}
