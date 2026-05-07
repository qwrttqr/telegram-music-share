<?php

namespace QwrttqrHTTP\Helpers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use QwrttqrHTTP\Interfaces\HttpBrokerInterface;

class HttpBroker implements HttpBrokerInterface
{
  private static ?HttpBroker $instance = null;

  public static function getInstance(): HttpBroker
  {
    if (self::$instance === null) {
      self::$instance = new HttpBroker();
    }

    return self::$instance;
  }

  public function createPsr7Request(): RequestInterface
  {
    return \QwrttqrHTTP\Http\Request::createFromGlobals();
  }

  public function createPsr7Response(): ResponseInterface
  {
    return new \QwrttqrHTTP\Http\Response();
  }

  public function sendResponse(ResponseInterface $response): void
  {
    http_response_code($response->getStatusCode());

    foreach ($response->getHeaders() as $name => $values) {
      foreach ($values as $value) {
        header("$name: $value", false);
      }
    }

    $body = (string)$response->getBody();
    echo $body;
  }
}