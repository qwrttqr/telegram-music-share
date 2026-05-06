<?php

namespace QwrttqrHTTP\Wrappers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class ControllerWrapper
{
  private RequestInterface $request;
  private ResponseInterface $response;

  public function __construct(RequestInterface $request, ResponseInterface $response)
  {
    $this->request = $request;
    $this->response = $response;
  }

  public function getRequest(): RequestInterface
  {
    return $this->request;
  }

  public function getResponse(): ResponseInterface
  {
    return $this->response;
  }

  public function setRequest(\QwrttqrHTTP\Http\Request $request): void
  {
    $this->request = $request;
  }

  public function setResponse(\QwrttqrHTTP\Http\Response $response): void
  {
    $this->response = $response;
  }

  public function json(array $data, int $status = 200): ResponseInterface
  {
    $this->response = $this->response->withStatus($status)->withHeader('Content-Type', 'application/json');
    $this->response->getBody()->write(json_encode($data));
    return $this->response;
  }

  public function html(string $content, int $status = 200): ResponseInterface
  {
    $this->response = $this->response->withStatus($status)->withHeader('Content-Type', 'text/html');
    $this->response->getBody()->write($content);
    return $this->response;
  }
}