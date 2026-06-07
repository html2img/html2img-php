<?php

declare(strict_types=1);

namespace Html2img\Request;

use InvalidArgumentException;

/**
 * A request to render an HTML document to an image.
 *
 * Maps directly to the body of `POST /api/html`. Only `html` is required.
 * Null values are omitted from the request so the server applies its own
 * defaults.
 */
final readonly class HtmlRequest
{
    /**
     * @param  string  $html  A complete HTML document to render. Inline CSS or reference fonts via `<link>` tags in the head.
     * @param  string|null  $css  Extra CSS injected after the document loads, on top of any styles in the HTML.
     * @param  int|null  $width  Viewport width in CSS pixels (1 to 5000).
     * @param  int|null  $height  Viewport height in CSS pixels (1 to 5000). Ignored when $fullpage is true.
     * @param  bool|null  $fullpage  Grow the image to the full rendered height of the document.
     * @param  int|null  $dpi  Device pixel ratio multiplier (1 to 4). 1 is standard, 2 is retina.
     * @param  string|null  $webhookUrl  Switch to async mode and POST the final image URL here once rendering finishes.
     * @param  int|null  $msDelay  Wait this many milliseconds after load before capturing (1 to 5000).
     * @param  string|null  $waitForSelector  Wait until this CSS selector appears in the DOM before capturing.
     */
    public function __construct(
        public string $html,
        public ?string $css = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?bool $fullpage = null,
        public ?int $dpi = null,
        public ?string $webhookUrl = null,
        public ?int $msDelay = null,
        public ?string $waitForSelector = null,
    ) {
        Guard::dimension('width', $width);
        Guard::dimension('height', $height);
        Guard::dpi($dpi);
        Guard::msDelay($msDelay);

        if ($html === '') {
            throw new InvalidArgumentException('The html field must not be empty.');
        }
    }

    /**
     * Build the JSON request body, omitting any null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'html' => $this->html,
            'css' => $this->css,
            'width' => $this->width,
            'height' => $this->height,
            'fullpage' => $this->fullpage,
            'dpi' => $this->dpi,
            'webhook_url' => $this->webhookUrl,
            'ms_delay' => $this->msDelay,
            'wait_for_selector' => $this->waitForSelector,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
