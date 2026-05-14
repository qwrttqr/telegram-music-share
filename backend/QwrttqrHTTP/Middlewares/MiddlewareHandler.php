<?php

namespace QwrttqrHTTP\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use QwrttqrHTTP\Helpers\MatchingRoute;
use QwrttqrHTTP\Interfaces\MiddlewareInterface;

class MiddlewareHandler
{
  private array $middlewares = []; // Initialize array
  private int $index;

  public function addMiddlewares(array $middlewares): static
  {
    foreach ($middlewares as $middleware) {
      $this->middlewares[] = $middleware;
    }
    return $this;
  }

  public function handle(
    MatchingRoute     $route,
    RequestInterface  $request,
    ResponseInterface $response,
    callable          $finalHandler
  ): ResponseInterface
  {
    $this->index = 0;
    return $this->next($route, $request, $response, $finalHandler);
  }

  private function next(
    MatchingRoute     $route,
    RequestInterface  $request,
    ResponseInterface $response,
    callable          $finalHandler
  ): ResponseInterface
  {
    // If no more middlewares, call the final handler (dispatch)
    if ($this->index >= count($this->middlewares)) {
      return $finalHandler($request, $response);
    }

    /** @var MiddlewareInterface $middleware */
    $middleware = $this->middlewares[$this->index];
    $this->index++;

    // Pass response to middleware and continue the chain
    return $middleware->fire(
      $route,
      $request,
      function ($request, $response) use ($finalHandler, $route) {
        return $this->next($route, $request, $response, $finalHandler);
      },
      $response
    );
  }
}