<?php

namespace QwrttqrHTTP\Exceptions;

class MissingParamException extends \Exception
{
  public function __construct($message, $code = 0, ?\Throwable $previous = null)
  {
    // make sure everything is assigned properly
    parent::__construct($message, $code, $previous);
  }
}