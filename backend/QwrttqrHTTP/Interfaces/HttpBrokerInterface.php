<?php

namespace QwrttqrHTTP\Interfaces;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpBrokerInterface
{
  public function createPsr7Request(): RequestInterface;

  public function createPsr7Response(): ResponseInterface;

  public function sendResponse(ResponseInterface $response): void;
}