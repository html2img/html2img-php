<?php

declare(strict_types=1);

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Html2img\Exception\AuthenticationException;
use Html2img\Exception\ConnectionException;
use Html2img\Exception\Html2imgException;
use Html2img\Exception\InsufficientCreditsException;
use Html2img\Exception\NotFoundException;
use Html2img\Exception\NotSubscribedException;
use Html2img\Exception\ServerException;
use Html2img\Exception\TimeoutException;
use Html2img\Exception\ValidationException;
use Html2img\Request\HtmlRequest;

/**
 * @param  array<string, mixed>  $body
 */
function callHtml(int $status, array $body): Closure
{
    $client = mockClient([jsonResponse($status, $body)]);

    return fn () => $client->html(new HtmlRequest(html: '<h1>Hi</h1>'));
}

dataset('error matrix', [
    'validation 400' => [400, ['error' => 'Validation failed', 'code' => 'validation_error', 'details' => []], ValidationException::class],
    'validation 422' => [422, ['error' => 'Validation failed', 'code' => 'validation_error', 'details' => []], ValidationException::class],
    'auth 401' => [401, ['error' => 'Invalid API key', 'code' => 'invalid_api_key'], AuthenticationException::class],
    'credits 402' => [402, ['error' => 'Insufficient credits', 'code' => 'insufficient_credits', 'credits_remaining' => 0], InsufficientCreditsException::class],
    'forbidden 403' => [403, ['error' => 'You must be subscribed to use this service', 'code' => 'not_subscribed'], NotSubscribedException::class],
    'not found 404' => [404, ['error' => 'Template not found', 'code' => 'template_not_found'], NotFoundException::class],
    'timeout 504' => [504, ['error' => 'Request timed out', 'code' => 'timeout_error'], TimeoutException::class],
    'server 500' => [500, ['error' => 'Service error', 'code' => 'service_error'], ServerException::class],
    'server 502' => [502, ['error' => 'Bad gateway'], ServerException::class],
    'unmapped 418' => [418, ['error' => 'I am a teapot'], Html2imgException::class],
]);

it('maps http status codes to typed exceptions', function (int $status, array $body, string $expected) {
    callHtml($status, $body)();
})->throws(Html2imgException::class)->with('error matrix');

it('maps each status to the precise exception subclass', function (int $status, array $body, string $expected) {
    try {
        callHtml($status, $body)();
        $this->fail('Expected an exception to be thrown.');
    } catch (Html2imgException $e) {
        /** @var class-string $expected */
        expect($e)->toBeInstanceOf($expected)
            ->and($e->statusCode())->toBe($status);
    }
})->with('error matrix');

it('carries the error code and payload on the exception', function () {
    try {
        callHtml(402, ['error' => 'Insufficient credits', 'code' => 'insufficient_credits', 'credits_remaining' => 0])();
    } catch (InsufficientCreditsException $e) {
        expect($e->errorCode())->toBe('insufficient_credits')
            ->and($e->getMessage())->toBe('Insufficient credits')
            ->and($e->payload())->toHaveKey('credits_remaining')
            ->and($e->creditsRemaining())->toBe(0);
    }
});

it('exposes per-field validation details', function () {
    try {
        callHtml(400, [
            'error' => 'Validation failed',
            'code' => 'validation_error',
            'details' => [
                'url' => ['The url field is required.'],
                'width' => ['The width must be between 1 and 5000.'],
            ],
        ])();
    } catch (ValidationException $e) {
        expect($e->details())->toBe([
            'url' => ['The url field is required.'],
            'width' => ['The width must be between 1 and 5000.'],
        ]);
    }
});

it('wraps a Guzzle ConnectException as a ConnectionException', function () {
    $client = mockClient([
        new ConnectException('Connection refused', new Request('POST', '/api/html')),
    ]);

    expect(fn () => $client->html(new HtmlRequest(html: '<h1>Hi</h1>')))
        ->toThrow(ConnectionException::class);
});

it('falls back to a status-based message when the body has no error text', function () {
    try {
        callHtml(500, [])();
    } catch (Html2imgException $e) {
        expect($e->getMessage())->toBe('The html2img API returned HTTP 500.');
    }
});
