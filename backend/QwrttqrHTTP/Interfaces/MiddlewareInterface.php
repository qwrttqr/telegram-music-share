<?php

namespace QwrttqrHTTP\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use QwrttqrHTTP\Helpers\MatchingRoute;

interface MiddlewareInterface
{
  public function fire(MatchingRoute $route, ServerRequestInterface $request, callable $next): ResponseInterface;
}