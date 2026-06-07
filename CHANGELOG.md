# Changelog

All notable changes to this package are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.0.0]: https://github.com/html2img/html2img-php/releases/tag/v1.0.0
