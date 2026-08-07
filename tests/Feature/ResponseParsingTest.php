<?php

declare(strict_types=1);

use Html2img\Request\HtmlRequest;
use Html2img\Request\ScreenshotRequest;
use Html2img\Response\RenderResponse;

it('parses a synchronous success envelope into a RenderResponse', function () {
    $client = mockClient([
        jsonResponse(200, [
            'success' => true,
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'expires_at' => '2026-08-14T09:16:39+00:00',
            'credits_remaining' => 4999,
            'url' => 'https://i.html2img.com/abc123def456.png',
        ]),
    ]);

    $response = $client->html(new HtmlRequest(html: '<h1>Hi</h1>'));

    expect($response)->toBeInstanceOf(RenderResponse::class)
        ->and($response->success)->toBeTrue()
        ->and($response->id)->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and($response->url)->toBe('https://i.html2img.com/abc123def456.png')
        ->and($response->expiresAt)->toBe('2026-08-14T09:16:39+00:00')
        ->and($response->creditsRemaining)->toBe(4999)
        ->and($response->isProcessing())->toBeFalse()
        ->and($response->status)->toBeNull();
});

it('treats a null or absent expires_at as null', function () {
    $client = mockClient([
        jsonResponse(200, [
            'success' => true,
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'expires_at' => null,
            'credits_remaining' => 4999,
            'url' => 'https://i.html2img.com/abc123def456.png',
        ]),
    ]);

    $response = $client->html(new HtmlRequest(html: '<h1>Hi</h1>'));

    expect($response->expiresAt)->toBeNull();
});

it('parses an async acceptance envelope', function () {
    $client = mockClient([
        jsonResponse(200, [
            'success' => true,
            'id' => '7c9e6679-7425-40de-944b-e07fc1f90ae7',
            'credits_remaining' => 4998,
            'status' => 'processing',
            'message' => 'Image generation started',
            'url' => null,
        ]),
    ]);

    $response = $client->screenshot(new ScreenshotRequest(
        url: 'https://example.com',
        webhookUrl: 'https://example.com/hook',
    ));

    expect($response->isProcessing())->toBeTrue()
        ->and($response->status)->toBe('processing')
        ->and($response->message)->toBe('Image generation started')
        ->and($response->url)->toBeNull();
});

it('treats credits_remaining as null when the API omits it', function () {
    $client = mockClient([
        jsonResponse(200, [
            'success' => true,
            'id' => 'abc',
            'url' => 'https://i.html2img.com/abc.png',
        ]),
    ]);

    $response = $client->html(new HtmlRequest(html: '<h1>Hi</h1>'));

    expect($response->creditsRemaining)->toBeNull();
});

it('exposes the raw payload', function () {
    $payload = [
        'success' => true,
        'id' => 'abc',
        'url' => 'https://i.html2img.com/abc.png',
        'undocumented_field' => 'kept',
    ];
    $client = mockClient([jsonResponse(200, $payload)]);

    $response = $client->html(new HtmlRequest(html: '<h1>Hi</h1>'));

    expect($response->raw())->toBe($payload)
        ->and($response->raw['undocumented_field'])->toBe('kept');
});
