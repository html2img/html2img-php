<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Html2img\Html2imgClient;
use Psr\Http\Message\RequestInterface;

/**
 * Build a client backed by Guzzle's MockHandler so no real network call is
 * made. The $history array is filled, by reference, with the outgoing
 * transactions so tests can assert the request that was sent.
 *
 * @param  list<Response|Throwable>  $queue
 * @param  array<int, mixed>  $history
 */
function mockClient(array $queue, array &$history = []): Html2imgClient
{
    $mock = new MockHandler($queue);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $guzzle = new Client([
        'handler' => $stack,
        'base_uri' => Html2imgClient::DEFAULT_BASE_URI,
    ]);

    return new Html2imgClient('test-key', httpClient: $guzzle);
}

/**
 * A JSON response with the given status and body.
 *
 * @param  array<string, mixed>  $body
 */
function jsonResponse(int $status, array $body): Response
{
    return new Response($status, ['Content-Type' => 'application/json'], json_encode($body, JSON_THROW_ON_ERROR));
}

/**
 * The most recently recorded outgoing request.
 *
 * @param  array<int, mixed>  $history
 */
function lastRequest(array $history): RequestInterface
{
    $last = end($history);
    $request = is_array($last) ? ($last['request'] ?? null) : null;

    if (! $request instanceof RequestInterface) {
        throw new RuntimeException('No request was recorded.');
    }

    return $request;
}

/**
 * The decoded JSON body of the last recorded request.
 *
 * @param  array<int, mixed>  $history
 * @return array<string, mixed>
 */
function lastRequestBody(array $history): array
{
    $decoded = json_decode((string) lastRequest($history)->getBody(), true);

    return is_array($decoded) ? $decoded : [];
}
