<?php

declare(strict_types=1);

use Html2img\Enum\Format;
use Html2img\Html2imgClient;
use Html2img\Request\HtmlRequest;
use Html2img\Request\ScreenshotRequest;

it('constructs with only an api key', function () {
    expect(new Html2imgClient('my-key'))->toBeInstanceOf(Html2imgClient::class);
});

it('still sends the api key header when a bare client is injected', function () {
    $history = [];
    // mockClient injects a Guzzle client that sets no default headers of its
    // own, proving the SDK adds X-API-Key on every request regardless.
    $client = mockClient([
        jsonResponse(200, ['success' => true, 'id' => 'x', 'url' => 'https://i.html2img.com/x.png']),
    ], $history);

    $client->html(new HtmlRequest(html: '<h1>Hi</h1>'));

    expect(lastRequest($history)->getHeaderLine('X-API-Key'))->toBe('test-key');
});

it('rejects an out-of-range dpi', function () {
    expect(fn () => new HtmlRequest(html: '<h1>Hi</h1>', dpi: 5))
        ->toThrow(InvalidArgumentException::class, 'The dpi must be between 1 and 4.');
});

it('rejects an out-of-range width', function () {
    expect(fn () => new ScreenshotRequest(url: 'https://example.com', width: 99999))
        ->toThrow(InvalidArgumentException::class, 'The width must be between 1 and 5000.');
});

it('rejects an out-of-range ms_delay', function () {
    expect(fn () => new HtmlRequest(html: '<h1>Hi</h1>', msDelay: 0))
        ->toThrow(InvalidArgumentException::class, 'The ms_delay must be between 1 and 5000.');
});

it('rejects empty required fields', function () {
    expect(fn () => new HtmlRequest(html: ''))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => new ScreenshotRequest(url: ''))
        ->toThrow(InvalidArgumentException::class);
});

it('accepts boundary values', function () {
    $request = new HtmlRequest(html: '<h1>Hi</h1>', width: 1, height: 5000, dpi: 4, msDelay: 5000, format: Format::Png);

    expect($request->toArray())->toBe([
        'html' => '<h1>Hi</h1>',
        'width' => 1,
        'height' => 5000,
        'dpi' => 4,
        'ms_delay' => 5000,
        'format' => 'png',
    ]);
});
