<?php

declare(strict_types=1);

use Html2img\Enum\Format;
use Html2img\Request\ScreenshotRequest;

it('posts to /api/screenshot with the api key header', function () {
    $history = [];
    $client = mockClient([
        jsonResponse(200, [
            'success' => true,
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'credits_remaining' => 4998,
            'url' => 'https://i.html2img.com/snap.png',
        ]),
    ], $history);

    $client->screenshot(new ScreenshotRequest(url: 'https://example.com'));

    $request = lastRequest($history);

    expect($request->getMethod())->toBe('POST')
        ->and($request->getUri()->getPath())->toBe('/api/screenshot')
        ->and($request->getHeaderLine('X-API-Key'))->toBe('test-key');
});

it('maps every screenshot option into the json body, including selector', function () {
    $history = [];
    $client = mockClient([
        jsonResponse(200, ['success' => true, 'id' => 'x', 'url' => 'https://i.html2img.com/x.png']),
    ], $history);

    $client->screenshot(new ScreenshotRequest(
        url: 'https://example.com',
        css: '.cookie-banner { display: none; }',
        width: 1200,
        height: 630,
        fullpage: false,
        selector: '#hero',
        dpi: 2,
        webhookUrl: 'https://example.com/hook',
        msDelay: 750,
        waitForSelector: '.chart-rendered',
        format: Format::Pdf,
    ));

    expect(lastRequestBody($history))->toBe([
        'url' => 'https://example.com',
        'css' => '.cookie-banner { display: none; }',
        'width' => 1200,
        'height' => 630,
        'fullpage' => false,
        'selector' => '#hero',
        'dpi' => 2,
        'webhook_url' => 'https://example.com/hook',
        'ms_delay' => 750,
        'wait_for_selector' => '.chart-rendered',
        'format' => 'pdf',
    ]);
});

it('returns the .pdf url untouched for a pdf render', function () {
    $history = [];
    $client = mockClient([
        jsonResponse(200, [
            'success' => true,
            'id' => 'x',
            'credits_remaining' => 499,
            'url' => 'https://i.html2img.com/image-1.pdf',
        ]),
    ], $history);

    $response = $client->screenshot(new ScreenshotRequest(
        url: 'https://example.com',
        format: Format::Pdf,
    ));

    expect(lastRequestBody($history))->toBe([
        'url' => 'https://example.com',
        'format' => 'pdf',
    ])->and($response->url)->toBe('https://i.html2img.com/image-1.pdf');
});
