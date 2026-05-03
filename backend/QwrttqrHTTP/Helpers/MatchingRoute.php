<?php

namespace QwrttqrHTTP\Helpers;

class MatchingRoute
{
  public string $class;
  public string $method;
  public array $pathParams;
  public array $queryParams;

  public function __construct(string $class, string $method, array $pathParams, array $queryParams)
  {
    $this->class = $class;
    $this->method = $method;
    $this->pathParams = $pathParams;
    $this->queryParams = $queryParams;
  }
}