<?php

declare(strict_types=1);

it('posts the data payload to /api/v1/templates/{slug}', function () {
    $history = [];
    $client = mockClient([
        jsonResponse(200, [
            'success' => true,
            'id' => '7c9e6679-7425-40de-944b-e07fc1f90ae7',
            'template' => 'invoice',
            'credits_remaining' => 4997,
            'url' => 'https://i.html2img.com/invoice.png',
        ]),
    ], $history);

    $response = $client->template('invoice', [
        'number' => 1042,
        'amount' => '$240.00',
    ]);

    $request = lastRequest($history);

    expect($request->getMethod())->toBe('POST')
        ->and($request->getUri()->getPath())->toBe('/api/v1/templates/invoice')
        ->and($request->getHeaderLine('X-API-Key'))->toBe('test-key')
        ->and(lastRequestBody($history))->toBe(['number' => 1042, 'amount' => '$240.00'])
        ->and($response->template)->toBe('invoice');
});

it('url-encodes the template slug', function () {
    $history = [];
    $client = mockClient([
        jsonResponse(200, ['success' => true, 'id' => 'x', 'template' => 'a b']),
    ], $history);

    $client->template('a b', []);

    expect(lastRequest($history)->getUri()->getPath())->toBe('/api/v1/templates/a%20b');
});
