<?php

declare(strict_types=1);

namespace Html2img\Request;

use Html2img\Enum\Format;
use InvalidArgumentException;

/**
 * A request to capture a screenshot of a live, publicly reachable URL.
 *
 * Maps directly to the body of `POST /api/screenshot`. Only `url` is
 * required. Null values are omitted from the request so the server applies
 * its own defaults.
 */
final readonly class ScreenshotRequest
{
    /**
     * @param  string  $url  The fully qualified, publicly reachable URL to capture.
     * @param  string|null  $css  Extra CSS injected after the page loads, for example to hide cookie banners.
     * @param  int|null  $width  Viewport width in CSS pixels (1 to 5000).
     * @param  int|null  $height  Viewport height in CSS pixels (1 to 5000). Ignored when $fullpage is true.
     * @param  bool|null  $fullpage  Capture the full scroll length of the page instead of only the viewport.
     * @param  string|null  $selector  CSS selector used to crop the capture to a single element.
     * @param  int|null  $dpi  Device pixel ratio multiplier (1 to 4). 1 is standard, 2 is retina.
     * @param  string|null  $webhookUrl  Switch to async mode and POST the final image URL here once rendering finishes.
     * @param  int|null  $msDelay  Wait this many milliseconds after load before capturing (1 to 5000).
     * @param  string|null  $waitForSelector  Wait until this CSS selector appears in the DOM before capturing.
     * @param  Format|null  $format  Output format. Defaults to PNG server-side.
     */
    public function __construct(
        public string $url,
        public ?string $css = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?bool $fullpage = null,
        public ?string $selector = null,
        public ?int $dpi = null,
        public ?string $webhookUrl = null,
        public ?int $msDelay = null,
        public ?string $waitForSelector = null,
        public ?Format $format = null,
    ) {
        Guard::dimension('width', $width);
        Guard::dimension('height', $height);
        Guard::dpi($dpi);
        Guard::msDelay($msDelay);

        if ($url === '') {
            throw new InvalidArgumentException('The url field must not be empty.');
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
            'url' => $this->url,
            'css' => $this->css,
            'width' => $this->width,
            'height' => $this->height,
            'fullpage' => $this->fullpage,
            'selector' => $this->selector,
            'dpi' => $this->dpi,
            'webhook_url' => $this->webhookUrl,
            'ms_delay' => $this->msDelay,
            'wait_for_selector' => $this->waitForSelector,
            'format' => $this->format?->value,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
