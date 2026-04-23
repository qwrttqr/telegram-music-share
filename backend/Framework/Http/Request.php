<?php

namespace Framework\Http;

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
      $this->headers['Host'] = [$uri->getHost()];
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
        $clone->headers['Host'] = [$host];
      }
    } else {
      if ((!isset($clone->headers['Host']) || empty($clone->headers['Host'])) && $host !== '') {
        $clone->headers['Host'] = [$host];
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
   * Case-sensitive checker for presence of desired header.
   * @param string $name
   * @return bool
   */
  public function hasHeader(string $name): bool
  {
    return isset($this->headers[$name]);
  }

  /**
   * Case-sensitive helper for getting headers.
   * @param string $name
   * @return array|string[]
   */
  public function getHeader(string $name): array
  {
    return $this->headers[$name] ?? [];
  }

  /**
   * Case-sensitive helper for getting header-line.
   * @param string $name
   * @return string
   */
  public function getHeaderLine(string $name): string
  {
    return implode(', ', $this->getHeader($name));
  }

  /**
   * Case-sensitive helper for setting headers.
   * @param string $name
   * @param $value
   * @return \Psr\Http\Message\MessageInterface
   */
  public function withHeader(string $name, $value): \Psr\Http\Message\MessageInterface
  {
    $clone = clone $this;
    $clone->headers[$name] = (array)$value;

    return $clone;
  }

  /**
   * Case-sensitive helper for adding value into already existing header.
   * @param string $name
   * @param $value
   * @return \Psr\Http\Message\MessageInterface
   */
  public function withAddedHeader(string $name, $value): \Psr\Http\Message\MessageInterface
  {
    $clone = clone $this;

    if (!isset($clone->headers[$name])) {
      $clone->headers[$name] = [];
    }
    $clone->headers[$name] = array_merge(
      $clone->headers[$name],
      (array)$value
    );

    return $clone;
  }

  public function withoutHeader(string $name): \Psr\Http\Message\MessageInterface
  {

  }

  public function getBody(): \Psr\Http\Message\StreamInterface
  {
    return $this->body;
  }

  public function withBody(\Psr\Http\Message\StreamInterface $body): \Psr\Http\Message\MessageInterface
  {
    $clone = clone $this;
    $this->body = $body;
    return $clone;
  }
}