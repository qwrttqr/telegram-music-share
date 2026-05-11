<?php

namespace QwrttqrHTTP\Middlewares;

use http\Env\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use QwrttqrHTTP\Helpers\MatchingRoute;
use QwrttqrHTTP\Interfaces\MiddlewareInterface;

class MiddlewareHandler
{
  private array $middlewares;
  private int $index;

  /**
   * @param MiddlewareInterface[] $middlewares
   * @return static
   */
  public function addMiddlewares(array $middlewares): static
  {
    foreach ($middlewares as $middleware) {
      $this->middlewares[] = $middleware;
    }
    return $this;
  }

  public function handle(MatchingRoute $route, ServerRequestInterface $request, callable $finalHandler): ResponseInterface
  {
    $this->index = 0;
    return $this->next($route, $request, $finalHandler);
  }

  private function next(MatchingRoute $route, ServerRequestInterface $request, callable $finalHandler): ResponseInterface
  {
    if ($this->index >= count($this->middlewares)) {
      return $finalHandler($route, $request);
    }
    /** @var MiddlewareInterface $middleware */
    $middleware = $this->middlewares[$this->index];
    $this->index++;

    return $middleware->fire($route, $request, function ($route, $request) use ($finalHandler) {
      return $this->next($route, $request, $finalHandler);
    });
  }
}