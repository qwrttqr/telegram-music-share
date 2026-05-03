<?php

namespace QwrttqrHTTP\Helpers;

use QwrttqrHTTP\Interfaces\RouteExpeditorInterface;

class RouteExpeditor implements RouteExpeditorInterface
{
  private static ?RouteExpeditor $instance = null;

  public static function getInstance(): RouteExpeditor
  {
    if (self::$instance === null) {
      self::$instance = new RouteExpeditor();
    }

    return self::$instance;
  }
  public function routeToRegexp(string $route): string
  {
    // Convert /user/{id} to #^/user/([^/]+)$#
    $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $route);
    return '#^' . $pattern . '$#';
  }

  public function extractParamNames(string $route): array
  {
    // Extract parameter names from /user/{id}/posts/{postId}
    // Returns: ['id', 'postId']
    preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route, $matches);
    return $matches[1];
  }
}