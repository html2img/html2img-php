# Changelog

All notable changes to this package are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.1] - 2026-09-23

### Changed

- The `InsufficientCreditsException` docblock no longer says credits run out
  for the current billing period. Free accounts have a one-off allowance of 50
  credits; paid plans renew each billing period.
- README: explain the free tier (50 one-off credits, renders hosted for seven
  days) and list every official package in a new "Other official packages"
  section.

## [2.0.0] - 2026-08-07

### Added

- `RenderResponse->expiresAt` - the ISO 8601 expiry of a free-tier render's
  hosted URL (`expires_at` in the raw payload). Null on paid plans, where
  renders stay hosted permanently.

### Removed

- **Breaking:** `RateLimitException`. The API has no rate limit and never
  returns a 429, so this exception could never be thrown. An unexpected 429
  would now surface as the base `Html2imgException`.

### Changed

- **Breaking:** `RenderResponse::__construct()` gained an `$expiresAt`
  parameter (after `$url`). Positional construction needs updating;
  `RenderResponse::fromArray()` and named arguments are unaffected.

## [1.1.0] - 2026-07-14

### Added

- `format` option (`Format::Png` or `Format::Pdf`) on `HtmlRequest` and
  `ScreenshotRequest`, now that PDF output is a documented API feature.
  `Format::Pdf` returns an A4 portrait vector PDF with selectable text;
  `width`, `height`, `dpi`, `fullpage` and `selector` are ignored by the API
  in PDF mode and the response `url` points at a `.pdf` file.

## [1.0.1] - 2026-06-08

### Removed

- The undocumented `format` option.

## [1.0.0] - 2026-06-07

### Added

- Initial release.
- `Html2img\Html2imgClient` with `html()`, `screenshot()` and `template()` methods.
- Readonly request objects `HtmlRequest` and `ScreenshotRequest`.
- Readonly `RenderResponse` exposing `success`, `id`, `url`, `creditsRemaining`,
  the async `status` and `message` fields, the optional `template` slug, and the
  raw payload.
- `Endpoint` enum.
- Exception hierarchy mapping HTTP status codes to typed exceptions:
  authentication, validation, insufficient credits, not subscribed, not found,
  rate limit, timeout, server error and connection failure.
- Support for an injected `GuzzleHttp\ClientInterface`.
- Pest test suite, PHPStan and Laravel Pint configuration.

[2.0.1]: https://github.com/html2img/html2img-php/releases/tag/v2.0.1
[1.1.0]: https://github.com/html2img/html2img-php/releases/tag/v1.1.0
[1.0.1]: https://github.com/html2img/html2img-php/releases/tag/v1.0.1
[1.0.0]: https://github.com/html2img/html2img-php/releases/tag/v1.0.0
