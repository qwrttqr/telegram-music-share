<?php

namespace QwrttqrHTTP\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface
{
  private string $method;
  private UriInterface $uri;
  private string $requestTarget = '';

  private array $headers = [];
  private string $protocolVersion = '1.1';
  private StreamInterface $body;

  public function __construct(string $method, UriInterface $uri)
  {
    $this->method = $method;
    $this->uri = $uri;

    if ($uri->getHost()) {
      $this->headers['host'] = [$uri->getHost()];
    }
  }

  public function getRequestTarget(): string
  {
    if ($this->requestTarget !== '') {
      return $this->requestTarget;
    }
    $path = $this->uri->getPath();
    $query = $this->uri->getQuery();
    if ($path === '') {
      $path = '/';
    }
    return $query ? "$path?$query" : $path;
  }

  public function withRequestTarget(string $requestTarget): RequestInterface
  {
    $clone = clone $this;
    $clone->requestTarget = $requestTarget;
    return $clone;
  }

  public function getMethod(): string
  {
    return $this->method;
  }

  public function withMethod(string $method): RequestInterface
  {
    if ($method === '') {
      throw new \InvalidArgumentException("Method cannot be empty");
    }
    $clone = clone $this;
    $clone->method = $method;
    return $clone;
  }

  public function getUri(): UriInterface
  {
    return $this->uri;
  }

  public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
  {
    $clone = clone $this;
    $clone->uri = $uri;

    $host = $uri->getHost();
    if (!$preserveHost) {
      if ($host !== '') {
        $clone->headers['host'] = [$host];
      }
    } else {
      if ((!isset($clone->headers['host']) || empty($clone->headers['host'])) && $host !== '') {
        $clone->headers['host'] = [$host];
      }
    }
    return $clone;
  }

  public function getProtocolVersion(): string
  {
    return $this->protocolVersion;
  }

  public function withProtocolVersion(string $version): \Psr\Http\Message\MessageInterface
  {
    $clone = clone $this;
    $clone->protocolVersion = $version;
    return $clone;
  }

  public function getHeaders(): array
  {
    return $this->headers;
  }

  /**
   * Case-insensitive checker for presence of desired header.
   * @param string $name
   * @return bool
   */
  public function hasHeader(string $name): bool
  {
    return isset($this->headers[strtolower($name)]);
  }

  /**
   * Case-insensitive helper for getting headers.
   * @param string $name
   * @return array|string[]
   */
  public function getHeader(string $name): array
  {
    return $this->headers[strtolower($name)] ?? [];
  }

  /**
   * Case-insensitive helper for getting header-line.
   * @param string $name
   * @return string
   */
  public function getHeaderLine(string $name): string
  {
    return implode(', ', $this->getHeader($name));
  }

  /**
   * Case-insensitive helper for setting headers.
   * @param string $name
   * @param $value
   * @return \Psr\Http\Message\MessageInterface
   */
  public function withHeader(string $name, $value): self
  {
    $clone = clone $this;
    $clone->headers[strtolower($name)] = (array)$value;

    return $clone;
  }

  /**
   * Case-insensitive helper for adding value into already existing header.
   * @param string $name
   * @param $value
   * @return self
   */
  public function withAddedHeader(string $name, $value): self
  {
    $clone = clone $this;
    $lowerName = strtolower($name);
    if (!isset($clone->headers[$lowerName])) {
      $clone->headers[$lowerName] = [];
    }
    $clone->headers[$lowerName] = array_merge(
      $clone->headers[$lowerName],
      (array)$value
    );

    return $clone;
  }

  public function withoutHeader(string $name): self
  {
    $clone = clone $this;
    unset($clone->headers[strtolower($name)]);
    return $clone;
  }

  public function getBody(): \Psr\Http\Message\StreamInterface
  {
    return $this->body;
  }

  public function withBody(\Psr\Http\Message\StreamInterface $body): self
  {
    $clone = clone $this;
    $clone->body = $body;
    return $clone;
  }

  public static function createFromGlobals(): self
  {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = Uri::createFromGlobals();

    $headers = [];

    foreach ($_SERVER as $key => $value) {
      if (strpos($key, 'HTTP_') === 0) {
        $headerName = str_replace('_', '-', substr($key, 5));
        $headers[$headerName] = $value;
      }
    }

    $body = new Stream(fopen('php://input', 'r'));

    $req = new self($method, $uri);
    foreach ($headers as $key => $value) {
      $req = $req->withHeader($key, $value);
    }
    return $req->withBody($body);
  }
}