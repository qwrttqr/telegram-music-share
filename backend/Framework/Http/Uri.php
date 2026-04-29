<?php

namespace Framework\Http;

use http\Exception\InvalidArgumentException;
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
    $this->user = $decodedUri['user'];
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
    if ($port !== '') {
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
    // TODO: Implement withHost() method.
  }

  public function withPort(?int $port): UriInterface
  {
    // TODO: Implement withPort() method.
  }

  public function withPath(string $path): UriInterface
  {
    // TODO: Implement withPath() method.
  }

  public function withQuery(string $query): UriInterface
  {
    // TODO: Implement withQuery() method.
  }

  public function withFragment(string $fragment): UriInterface
  {
    // TODO: Implement withFragment() method.
  }

  public function __toString(): string
  {
    // TODO: Implement __toString() method.
  }
}