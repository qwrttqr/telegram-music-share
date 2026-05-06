<?php

namespace QwrttqrHTTP\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{

  private const STANDARD_PORTS = [
    'http' => 80,
    'https' => 443
  ];

  private string $scheme = '';
  private string $user = '';
  private ?string $password = null;
  private string $host = '';
  private ?int $port = null;
  private string $path = '';
  private string $query = '';
  private string $fragment = '';

  public function __construct(string $uri)
  {
    if ($uri === '') {
      return;
    }

    $decodedUri = parse_url($uri);

    if ($decodedUri === false) {
      throw new InvalidArgumentException("Malformed URL \"$uri\"");
    }

    $this->scheme = isset($decodedUri['scheme']) ? strtolower($decodedUri['scheme']) : '';
    $this->user = $decodedUri['user'] ?? '';
    $this->password = $decodedUri['pass'] ?? null;
    $this->host = isset($decodedUri['host']) ? strtolower($decodedUri['host']) : '';
    $this->port = $decodedUri['port'] ?? null;
    $this->path = $decodedUri['path'] ?? '';
    $this->query = $decodedUri['query'] ?? '';
    $this->fragment = $decodedUri['fragment'] ?? '';
  }

  public function getScheme(): string
  {
    return $this->scheme;
  }

  /**
   * Authority like:
   * <pre>
   *   [user:password@]host[:port]
   * </pre>
   * @return string
   */
  public function getAuthority(): string
  {
    $authority = $this->host;
    if ($authority === '') {
      return '';
    }

    $userInfo = $this->getUserInfo();
    if ($userInfo !== '') {
      $authority = $userInfo . '@' . $authority;
    }

    $port = $this->getPort();
    if ($port) {
      $authority .= ':' . $port;
    }

    return $authority;
  }

  /**
   * User info like user[:password]
   * @return string
   */
  public function getUserInfo(): string
  {
    $userInfo = $this->user;
    if ($userInfo === '') {
      return '';
    }

    $password = $this->password;
    if ($password !== null) {
      $userInfo .= ':' . $password;
    }

    return $userInfo;
  }

  public function getHost(): string
  {
    return $this->host;
  }

  public function getPort(): ?int
  {
    $port = $this->port;

    if ($port === null) {
      return null;
    }

    $standardPort = self::STANDARD_PORTS[strtolower($this->scheme)];

    return ($standardPort === $this->port) ? null : $port;
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function getQuery(): string
  {
    return $this->query;
  }

  public function getFragment(): string
  {
    return $this->fragment;
  }

  public function withScheme(string $scheme): UriInterface
  {
    $clone = clone $this;
    $clone->scheme = strtolower($scheme);
    return $clone;
  }

  public function withUserInfo(string $user, ?string $password = null): UriInterface
  {
    $clone = clone $this;
    $clone->user = $user;
    $clone->password = ($user !== '' ? $password : null);
    return $clone;
  }

  public function withHost(string $host): UriInterface
  {
    $clone = clone $this;
    $clone->host = strtolower($host);
    return $clone;
  }

  public function withPort(?int $port): UriInterface
  {
    $clone = clone $this;
    $clone->port = $port;
    return $clone;
  }

  public function withPath(string $path): UriInterface
  {
    $clone = clone $this;
    $clone->path = $path;
    return $clone;
  }

  public function withQuery(string $query): UriInterface
  {
    $clone = clone $this;
    $clone->query = $query;
    return $clone;
  }

  public function withFragment(string $fragment): UriInterface
  {
    $clone = clone $this;
    $clone->fragment = $fragment;
    return $clone;
  }

  public function __toString(): string
  {
    $stringUri = '';
    if ($this->scheme !== '') {
      $stringUri .= $this->scheme . ':';
    }
    $stringUri .= $this->scheme . ':';
    $authority = $this->getAuthority();
    if ($authority !== '') {
      $stringUri .= '//' . $authority;
    }
    $stringUri .= $this->path;
    if ($this->query !== '') {
      $stringUri .= '?' . $this->query;
    }
    if ($this->fragment !== '') {
      $stringUri .= '#' . $this->fragment;
    }
    return $stringUri;
  }

  public static function createFromGlobals(): self
  {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return new self($uri);
  }
}