<?php

namespace QwrttqrHTTP\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use QwrttqrHTTP\Helpers\MatchingRoute;

interface MiddlewareInterface
{
  public function fire(MatchingRoute $route, RequestInterface $request, callable $next, ResponseInterface $response): ResponseInterface;
}