<?php

declare(strict_types=1);

namespace Html2img\Enum;

/**
 * Output format for a render.
 *
 * Defaults to PNG server-side when omitted from the request.
 */
enum Format: string
{
    case Png = 'png';
    case Pdf = 'pdf';
}
