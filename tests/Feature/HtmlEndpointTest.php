<?php

declare(strict_types=1);

use Html2img\Request\HtmlRequest;

it('posts to /api/html with the api key header', function () {
    $history = [];
    $client = mockClient([
        jsonResponse(200, [
            'success' => true,
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'credits_remaining' => 4999,
            'url' => 'https://i.html2img.com/abc123def456.png',
        ]),
    ], $history);

    $client->html(new HtmlRequest(html: '<h1>Hello</h1>'));

    $request = lastRequest($history);

    expect($request->getMethod())->toBe('POST')
        ->and($request->getUri()->getPath())->toBe('/api/html')
        ->and($request->getUri()->getHost())->toBe('app.html2img.com')
        ->and($request->getHeaderLine('X-API-Key'))->toBe('test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/json');
});

it('maps every html option into the json body in snake_case', function () {
    $history = [];
    $client = mockClient([
        jsonResponse(200, ['success' => true, 'id' => 'x', 'url' => 'https://i.html2img.com/x.png']),
    ], $history);

    $client->html(new HtmlRequest(
        html: '<h1>Invoice</h1>',
        css: 'body { background: #fff; }',
        width: 794,
        height: 1123,
        fullpage: true,
        dpi: 2,
        webhookUrl: 'https://example.com/hook',
        msDelay: 500,
        waitForSelector: '.ready',
    ));

    expect(lastRequestBody($history))->toBe([
        'html' => '<h1>Invoice</h1>',
        'css' => 'body { background: #fff; }',
        'width' => 794,
        'height' => 1123,
        'fullpage' => true,
        'dpi' => 2,
        'webhook_url' => 'https://example.com/hook',
        'ms_delay' => 500,
        'wait_for_selector' => '.ready',
    ]);
});

it('omits null html options from the json body', function () {
    $history = [];
    $client = mockClient([
        jsonResponse(200, ['success' => true, 'id' => 'x', 'url' => 'https://i.html2img.com/x.png']),
    ], $history);

    $client->html(new HtmlRequest(html: '<h1>Hi</h1>'));

    expect(lastRequestBody($history))->toBe(['html' => '<h1>Hi</h1>']);
});
