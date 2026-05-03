<?php

namespace QwrttqrHTTP\Helpers;

class MatchingRoute
{
  public string $class;
  public string $method;
  public array $params;

  public function __construct(string $class, string $method, array $params)
  {
    $this->class = $class;
    $this->method = $method;
    $this->params = $params;
  }
}