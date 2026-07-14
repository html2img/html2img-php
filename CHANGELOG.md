# Changelog

All notable changes to this package are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.1.0]: https://github.com/html2img/html2img-php/releases/tag/v1.1.0
[1.0.1]: https://github.com/html2img/html2img-php/releases/tag/v1.0.1
[1.0.0]: https://github.com/html2img/html2img-php/releases/tag/v1.0.0
