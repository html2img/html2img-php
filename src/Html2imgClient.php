<?php

declare(strict_types=1);

namespace Html2img;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Html2img\Enum\Endpoint;
use Html2img\Exception\AuthenticationException;
use Html2img\Exception\ConnectionException;
use Html2img\Exception\Html2imgException;
use Html2img\Exception\InsufficientCreditsException;
use Html2img\Exception\NotFoundException;
use Html2img\Exception\NotSubscribedException;
use Html2img\Exception\RateLimitException;
use Html2img\Exception\ServerException;
use Html2img\Exception\TimeoutException;
use Html2img\Exception\ValidationException;
use Html2img\Request\HtmlRequest;
use Html2img\Request\ScreenshotRequest;
use Html2img\Response\RenderResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Client for the html2img.com API.
 *
 * Render HTML documents you control, capture live URLs, or render named
 * templates, each returning a {@see RenderResponse}. Every failure surfaces
 * as a {@see Html2imgException}; no raw Guzzle exception escapes.
 */
final class Html2imgClient
{
    public const string DEFAULT_BASE_URI = 'https://app.html2img.com';

    public const float DEFAULT_TIMEOUT = 35.0;

    private readonly ClientInterface $http;

    /**
     * @param  string  $apiKey  Your html2img API key, sent as the `X-API-Key` header.
     * @param  string  $baseUri  Base URI of the API. Override only for testing or a private deployment.
     * @param  float  $timeout  Request timeout in seconds. Defaults to just over the 30 second sync render budget.
     * @param  ClientInterface|null  $httpClient  An optional pre-configured Guzzle client. When supplied it is used as is; the SDK still sends the `X-API-Key`, `Accept` and `Content-Type` headers on every request. When null, a client is built with the base URI, timeout and default headers.
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUri = self::DEFAULT_BASE_URI,
        private readonly float $timeout = self::DEFAULT_TIMEOUT,
        ?ClientInterface $httpClient = null,
    ) {
        $this->http = $httpClient ?? new Client([
            'base_uri' => $this->baseUri,
            'timeout' => $this->timeout,
            'headers' => $this->defaultHeaders(),
        ]);
    }

    /**
     * Render an HTML document to an image.
     */
    public function html(HtmlRequest $request): RenderResponse
    {
        return $this->send(Endpoint::Html, $request->toArray());
    }

    /**
     * Capture a screenshot of a live URL.
     */
    public function screenshot(ScreenshotRequest $request): RenderResponse
    {
        return $this->send(Endpoint::Screenshot, $request->toArray());
    }

    /**
     * Render a named template from a JSON data payload.
     *
     * @param  string  $slug  The template slug, for example `invoice`.
     * @param  array<string, mixed>  $data  The template data, validated server-side per template.
     */
    public function template(string $slug, array $data = []): RenderResponse
    {
        return $this->send(Endpoint::Template, $data, $slug);
    }

    /**
     * Send a request and map the outcome onto a response or a typed exception.
     *
     * @param  array<string, mixed>  $body
     *
     * @throws Html2imgException
     */
    private function send(Endpoint $endpoint, array $body, ?string $slug = null): RenderResponse
    {
        try {
            $response = $this->http->request('POST', $endpoint->path($slug), [
                'headers' => $this->defaultHeaders(),
                'json' => $body,
            ]);
        } catch (ConnectException $e) {
            throw new ConnectionException(
                'Could not reach the html2img API: '.$e->getMessage(),
                previous: $e,
            );
        } catch (BadResponseException $e) {
            throw $this->mapResponse($e->getResponse(), $e);
        } catch (GuzzleException $e) {
            throw new ConnectionException(
                'The request to the html2img API failed: '.$e->getMessage(),
                previous: $e,
            );
        }

        return RenderResponse::fromArray($this->decode($response));
    }

    /**
     * Map an error response onto the matching exception type.
     */
    private function mapResponse(ResponseInterface $response, GuzzleException $previous): Html2imgException
    {
        $status = $response->getStatusCode();
        $payload = $this->decode($response);

        $errorCode = isset($payload['code']) && is_string($payload['code']) ? $payload['code'] : null;
        $message = $this->messageFrom($payload, $status);

        return match (true) {
            $status === 400, $status === 422 => new ValidationException(
                $message,
                $this->detailsFrom($payload),
                $status,
                $payload,
                $errorCode,
                $previous,
            ),
            $status === 401 => new AuthenticationException($message, $status, $payload, $errorCode, $previous),
            $status === 402 => new InsufficientCreditsException($message, $status, $payload, $errorCode, $previous),
            $status === 403 => new NotSubscribedException($message, $status, $payload, $errorCode, $previous),
            $status === 404 => new NotFoundException($message, $status, $payload, $errorCode, $previous),
            $status === 429 => new RateLimitException($message, $status, $payload, $errorCode, $previous),
            $status === 408, $status === 504 => new TimeoutException($message, $status, $payload, $errorCode, $previous),
            $status >= 500 => new ServerException($message, $status, $payload, $errorCode, $previous),
            default => new Html2imgException($message, $status, $payload, $errorCode, $previous),
        };
    }

    /**
     * Derive a human-readable message from the response body.
     *
     * @param  array<string, mixed>  $payload
     */
    private function messageFrom(array $payload, int $status): string
    {
        foreach (['error', 'message'] as $key) {
            if (isset($payload[$key]) && is_string($payload[$key]) && $payload[$key] !== '') {
                return $payload[$key];
            }
        }

        return "The html2img API returned HTTP {$status}.";
    }

    /**
     * Extract the per-field validation messages from a validation response.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, list<string>>
     */
    private function detailsFrom(array $payload): array
    {
        $details = $payload['details'] ?? null;

        if (! is_array($details)) {
            return [];
        }

        $result = [];

        foreach ($details as $field => $messages) {
            $result[(string) $field] = array_values(array_map(
                static fn (mixed $message): string => (string) $message,
                is_array($messages) ? $messages : [$messages],
            ));
        }

        return $result;
    }

    /**
     * Decode a JSON response body into an array, tolerating empty or
     * malformed bodies.
     *
     * @return array<string, mixed>
     */
    private function decode(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * The headers sent on every request.
     *
     * @return array<string, string>
     */
    private function defaultHeaders(): array
    {
        return [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}
