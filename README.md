[![html2img — HTML to image API, rendered in real Chrome](https://html2img.com/og-image.png)](https://html2img.com)

# html2img PHP client

[![Packagist Version](https://img.shields.io/packagist/v/html2img/html2img-php)](https://packagist.org/packages/html2img/html2img-php)
[![PHP Version](https://img.shields.io/packagist/php-v/html2img/html2img-php)](https://packagist.org/packages/html2img/html2img-php)
[![Total Downloads](https://img.shields.io/packagist/dt/html2img/html2img-php)](https://packagist.org/packages/html2img/html2img-php)
[![License](https://img.shields.io/packagist/l/html2img/html2img-php)](LICENSE)

The official PHP client for the [html2img.com](https://html2img.com) API. Turn HTML and CSS into images, capture screenshots of live URLs, and render named templates, all returning a typed response object.

Every render runs in real Chrome, so flexbox, grid, custom properties, web fonts and inline JavaScript behave exactly as they do in the browser. The package is framework-agnostic and built on Guzzle, so it works in plain PHP and inside any framework. The full API reference lives in the [documentation](https://html2img.com/docs).

## What you can build

- **Open Graph and social images**, generated per page or post. See the [Open Graph image template](https://html2img.com/templates/open-graph-image) and [Twitter/X post template](https://html2img.com/templates/twitter-post).
- **Business documents** such as [invoices](https://html2img.com/templates/invoice-image), [receipts](https://html2img.com/templates/receipt-image), [event tickets](https://html2img.com/templates/event-ticket) and [certificates](https://html2img.com/templates/certificate-of-completion).
- **Developer assets** such as [code screenshots](https://html2img.com/templates/code-screenshot) and [GitHub social previews](https://html2img.com/templates/github-social-preview).
- **URL screenshots**, full page or cropped to a single element, with CSS injection to hide cookie banners and chat widgets before capture.

Browse the [full template library](https://html2img.com/templates), or try the no-signup [browser tools](https://html2img.com/tools) to see the output before you write any code.

## Requirements

- PHP 8.3 or newer
- A html2img API key, issued per account from your [dashboard](https://app.html2img.com/register)

## Installation

```bash
composer require html2img/html2img-php
```

## Quick start

```php
use Html2img\Html2imgClient;
use Html2img\Request\HtmlRequest;

$client = new Html2imgClient('your-api-key');

$response = $client->html(new HtmlRequest(
    html: '<!doctype html><html><body><h1>Hello</h1></body></html>',
    width: 1200,
    height: 630,
));

echo $response->url; // https://i.html2img.com/abc123def456.png
```

The API returns a JSON envelope containing the CDN URL of the generated image, not the raw bytes, so you can cache and re-serve it from your own infrastructure. New to the API? Start with the [getting started guide](https://html2img.com/docs/getting-started).

## Configuration

The constructor takes the API key plus optional overrides:

```php
use Html2img\Html2imgClient;

$client = new Html2imgClient(
    apiKey: 'your-api-key',
    baseUri: 'https://app.html2img.com', // default
    timeout: 35.0,                        // seconds, default
);
```

Authentication is sent on every request as the `X-API-Key` header. See the [authentication docs](https://html2img.com/docs/authentication) for issuing and rotating keys.

### Injecting your own Guzzle client

Pass a pre-configured `GuzzleHttp\ClientInterface` to reuse your own middleware,
retry strategy or logging. The SDK still adds the `X-API-Key`, `Accept` and
`Content-Type` headers on every request.

```php
use GuzzleHttp\Client;
use Html2img\Html2imgClient;

$guzzle = new Client([
    'base_uri' => 'https://app.html2img.com',
    'timeout' => 60,
    // your own handler stack, middleware, proxy settings, etc.
]);

$client = new Html2imgClient('your-api-key', httpClient: $guzzle);
```

## Usage

### Render HTML

`POST /api/html`. Send a complete HTML document and get back an image of the
rendered result. Inline your CSS in a `<style>` block, or reference remote
stylesheets and web fonts via `<link>` tags in the document head. See the
[`html` parameter docs](https://html2img.com/docs/parameters/html) for the full input.

```php
use Html2img\Request\HtmlRequest;

$response = $client->html(new HtmlRequest(
    html: $document,
    css: 'body { background: #0f172a; color: #fff; }', // injected after load
    width: 794,
    fullpage: true,
    dpi: 2,          // retina
));
```

### Capture a screenshot

`POST /api/screenshot`. Fetch a public URL in a real browser and capture it.
Use `selector` to crop to a single element, and `css` to hide cookie banners or
chat widgets before the capture. See the [`url` parameter docs](https://html2img.com/docs/parameters/url).

```php
use Html2img\Request\ScreenshotRequest;

$response = $client->screenshot(new ScreenshotRequest(
    url: 'https://example.com',
    width: 1200,
    height: 630,
    selector: '#hero',
    css: '.cookie-banner, .intercom-launcher { display: none !important; }',
    dpi: 2,
));
```

### Render a template

`POST /api/v1/templates/{slug}`. Render one of your named templates from a JSON
data payload. The data is validated server-side per template. [Browse the
templates](https://html2img.com/templates) to find a slug.

```php
$response = $client->template('invoice', [
    'number' => 1042,
    'amount' => '$240.00',
    'due_date' => '2026-07-01',
]);

echo $response->template; // invoice
echo $response->url;
```

## Options

Both `HtmlRequest` and `ScreenshotRequest` accept the following. Any option left
null is omitted from the request, so the server applies its own default. The
complete reference is in the [parameter docs](https://html2img.com/docs/parameters).

| Option             | Type      | Notes                                                        |
| ------------------ | --------- | ------------------------------------------------------------ |
| `css`              | string    | Extra CSS injected after the page loads.                     |
| `width`            | int       | Viewport width in CSS pixels (1 to 5000).                    |
| `height`           | int       | Viewport height in CSS pixels (1 to 5000). Ignored when `fullpage` is true. |
| `fullpage`         | bool      | Capture the full scroll length instead of the viewport.      |
| `dpi`              | int       | Device pixel ratio, 1 to 4. Use 2 for retina.                |
| `webhookUrl`       | string    | Switch to async delivery (see below).                        |
| `msDelay`          | int       | Wait this many milliseconds after load before capturing (1 to 5000). |
| `waitForSelector`  | string    | Wait until this CSS selector appears before capturing.       |

`ScreenshotRequest` also accepts `selector` (string) to crop the capture to a
single element. `HtmlRequest` does not, since you control the markup.

Custom fonts are loaded by referencing them with `<link>` tags in your HTML
document head, or by linking a web font from your captured page.

## The response

Every method returns a readonly `Html2img\Response\RenderResponse`:

```php
$response->success;          // bool
$response->id;               // string|null, the render id
$response->url;              // string|null, the CDN URL of the image
$response->creditsRemaining; // int|null, credits left after this call
$response->status;           // string|null, "processing" for async jobs
$response->message;          // string|null
$response->template;         // string|null, the template slug, when applicable
$response->isProcessing();   // bool
$response->raw();            // array, the full decoded JSON payload
```

### Asynchronous delivery

Synchronous requests have a 30 second budget. For captures likely to exceed it,
pass a `webhookUrl`. The API responds immediately with `status: "processing"`
and `url: null`, then POSTs the final image URL to your endpoint once rendering
finishes. See the [`webhook_url` docs](https://html2img.com/docs/parameters/webhook-url).

```php
$response = $client->screenshot(new ScreenshotRequest(
    url: 'https://example.com/long-report',
    fullpage: true,
    webhookUrl: 'https://your-app.example.com/hooks/html2img',
));

if ($response->isProcessing()) {
    // The final URL will arrive at your webhook, not on this response.
}
```

## Error handling

Every failure throws an `Html2img\Exception\Html2imgException`. Catch that single
type to handle any error, or catch a specific subclass. No raw Guzzle exception
escapes the client.

```php
use Html2img\Exception\Html2imgException;
use Html2img\Exception\ValidationException;
use Html2img\Exception\InsufficientCreditsException;

try {
    $response = $client->html(new HtmlRequest(html: $document));
} catch (ValidationException $e) {
    // 400 or 422: inspect the per-field messages
    foreach ($e->details() as $field => $messages) {
        // ...
    }
} catch (InsufficientCreditsException $e) {
    // 402: out of credits
    $left = $e->creditsRemaining();
} catch (Html2imgException $e) {
    // anything else
    $e->statusCode(); // int|null
    $e->errorCode();  // string|null, the API "code" field
    $e->payload();    // array, the decoded body
}
```

| Exception                       | When                                                  |
| ------------------------------- | ----------------------------------------------------- |
| `AuthenticationException`       | 401, missing or invalid API key.                      |
| `InsufficientCreditsException`  | 402, no credits remaining.                            |
| `NotSubscribedException`        | 403, no active subscription.                          |
| `NotFoundException`             | 404, for example an unknown template slug.            |
| `ValidationException`           | 400 or 422, with `details()` per field.               |
| `RateLimitException`            | 429, rate or quota exceeded.                          |
| `TimeoutException`              | 504, the synchronous render budget was exceeded.      |
| `ServerException`               | 5xx, an unexpected renderer error.                    |
| `ConnectionException`           | the request never reached a response.                 |
| `Html2imgException`             | base type for all of the above.                       |

## Other languages

Anything that can make an HTTP request works with the API. There are worked
guides for [Laravel](https://html2img.com/docs/usage/laravel),
[Ruby on Rails](https://html2img.com/docs/usage/rails),
[Python](https://html2img.com/docs/usage/python),
[JavaScript and Node.js](https://html2img.com/docs/usage/javascript),
[React](https://html2img.com/docs/usage/react) and
[Vue](https://html2img.com/docs/usage/vue).

## Development

This package uses [ddev](https://ddev.com) for a containerised PHP environment. It is optional,
and you can use vanilla PHP or whatever you use for local dev if you prefer.

```bash
ddev composer install
ddev exec vendor/bin/pest      # tests
ddev exec vendor/bin/phpstan analyse
ddev exec vendor/bin/pint --test
```

## Links

[Website](https://html2img.com) · [Documentation](https://html2img.com/docs) · [Templates](https://html2img.com/templates) · [Tools](https://html2img.com/tools) · [Features](https://html2img.com/features) · [Comparisons](https://html2img.com/compare) · [Articles](https://html2img.com/articles) · [Pricing](https://html2img.com/pricing)

## Licence

MIT. See [LICENSE](LICENSE).