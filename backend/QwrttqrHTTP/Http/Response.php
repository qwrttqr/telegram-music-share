<?php

namespace QwrttqrHTTP\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
  private string $protocolVersion = '1.1';
  private array $headers = [];
  private StreamInterface $body;
  private int $statusCode = 200;
  private string $reasonPhrase = '';

  /**
   * Standard HTTP reason phrases mapped to their status codes.
   * PSR-7 doesn't require these, but they're conventional defaults.
   */
  private const REASON_PHRASES = [
    200 => 'OK',
    201 => 'Created',
    204 => 'No Content',
    301 => 'Moved Permanently',
    302 => 'Found',
    304 => 'Not Modified',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    422 => 'Unprocessable Entity',
    429 => 'Too Many Requests',
    500 => 'Internal Server Error',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
  ];

  public function __construct(
    int              $statusCode = 200,
    array            $headers = [],
    ?StreamInterface $body = null
  )
  {
    $this->statusCode = $statusCode;
    $this->body = $body ?? new Stream(fopen('php://temp', 'r+'));
    foreach ($headers as $name => $value) {
      $this->headers[strtolower($name)] = $value;
    }
    $this->reasonPhrase = $this::REASON_PHRASES[$statusCode];
  }

  public function getProtocolVersion(): string
  {
    return $this->protocolVersion;
  }

  public function withProtocolVersion(string $version): static
  {
    $clone = clone $this;
    $clone->protocolVersion = $version;
    return $clone;
  }

  public function getHeaders(): array
  {
    return $this->headers;
  }

  public function hasHeader(string $name): bool
  {
    return isset($this->headers[$name]);
  }

  public function getHeader(string $name): array
  {
    return $this->headers[strtolower($name)] ?? [];
  }

  public function getHeaderLine(string $name): string
  {
    return implode(', ', $this->headers[strtolower($name)]);
  }

  public function withHeader(string $name, $value): static
  {
    $clone = clone $this;
    $clone->headers[strtolower($name)] = is_array($value) ? $value : [$value];
    return $clone;
  }

  public function withAddedHeader(string $name, $value): static
  {
    $clone = clone $this;
    $lower = strtolower($name);
    $val = is_array($value) ? $value : [$value];
    $clone->headers[$lower] = array_merge($this->headers[$lower] ?? [], $val);
    return $clone;
  }

  public function withoutHeader(string $name): static
  {
    $clone = clone $this;
    unset($clone->headers[strtolower($name)]);
    return $clone;
  }

  public function getBody(): \Psr\Http\Message\StreamInterface
  {
    return $this->body;
  }

  public function withBody(\Psr\Http\Message\StreamInterface $body): static
  {
    $clone = clone $this;
    $clone->body = $body;
    return $clone;
  }

  public function getStatusCode(): int
  {
    return $this->statusCode;
  }

  public function withStatus(int $code, string $reasonPhrase = ''): static
  {
    if ($code < 100 || $code > 599) {
      throw new \InvalidArgumentException('Incorrect status code');
    }
    $clone = clone $this;
    $clone->statusCode = $code;
    $clone->reasonPhrase = $reasonPhrase !== '' ? $reasonPhrase : self::REASON_PHRASES[$code] ?? '';
    return $clone;
  }

  public function getReasonPhrase(): string
  {
    return $this->reasonPhrase;
  }
}