<?php

namespace Framework\src;
#[\Attribute(\Attribute::TARGET_METHOD)]
class Route
{
  public function __construct(public string $path, public string $method = 'GET')
  {
    $this->$path = $path;
    $this->$method = $method;
  }
}