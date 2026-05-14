<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use QwrttqrHTTP\Helpers\MatchingRoute;
use QwrttqrHTTP\Interfaces\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{

  public function fire(MatchingRoute $route, RequestInterface $request, callable $next, ResponseInterface $response): ResponseInterface
  {
    echo $request, $response;
    return $response;
  }
}