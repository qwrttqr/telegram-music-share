<?php

namespace QwrttqrHTTP\Exceptions;
class RouteNotFoundException extends \Exception
{
  public function __construct($message, $code = 0, ?\Throwable $previous = null) {
    // make sure everything is assigned properly
    parent::__construct($message, $code, $previous);
  }
  public function __toString() {
    return "Route was not found: [$this->route]";
  }
}