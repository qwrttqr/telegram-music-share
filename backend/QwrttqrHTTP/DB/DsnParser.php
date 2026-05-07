<?php

namespace QwrttqrHTTP\DB;

class DsnParser
{
  private static ?DsnParser $instance = null;

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new DsnParser();
    }
    return self::$instance;
  }
  public static function parse(string $dsn): array
  {
    $parsed = parse_url($dsn);

    if (!$parsed) {
      throw new \InvalidArgumentException("Invalid DSN format");

    }

    $scheme = $parsed['scheme'] ?? null;
    $driver = self::convertSchemeToDriver($scheme);

    $params = [
      'driver' => $driver,
      'host' => $parsed['host'] ?? null,
      'port' => $parsed['port'] ?? null,
      'user' => $parsed['user'] ?? null,
      'password' => $parsed['pass'] ?? null,
      'dbname' => ltrim($parsed['path'] ?? '', '/')
    ];

    if (isset($parsed['query'])) {
      parse_str($parsed['query'], $queryParams);

      // Handle special parameters
      if (isset($queryParams['charset'])) {
        $params['charset'] = $queryParams['charset'];
      }
      if (isset($queryParams['sslmode'])) {
        $params['sslmode'] = $queryParams['sslmode'];
      }
      if (isset($queryParams['unix_socket'])) {
        $params['unix_socket'] = $queryParams['unix_socket'];
      }
    }
    return array_filter($params, function($value) {
      return $value !== null;
    });
  }

  public static function convertSchemeToDriver(string $scheme): string
  {
    return match ($scheme) {
      'mysql' => 'pdo_mysql',
      'pgsql', 'postgres', 'postgresql' => 'pdo_pgsql',
      'sqlite' => 'pdo_sqlite',
      'sqlsrv' => 'pdo_sqlsrv'
    };
  }
}