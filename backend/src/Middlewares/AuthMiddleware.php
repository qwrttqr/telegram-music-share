<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use QwrttqrHTTP\Helpers\MatchingRoute;
use QwrttqrHTTP\Interfaces\MiddlewareInterface;
use App\Exceptions\UnauthorizedException;

class AuthMiddleware implements MiddlewareInterface
{

  public function fire(MatchingRoute $route, RequestInterface $request, callable $next, ResponseInterface $response): ResponseInterface
  {
    $authorizationHeader = $request->getHeader('Authorization')[0];
    if (str_starts_with($authorizationHeader, 'Bearer ')) {
      $token = substr($authorizationHeader, 7);

      $secret = $_ENV['JWT_SECRET'];
      list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = explode('.', $token);

      $signature = $this->base64UrlDecode($base64UrlSignature);
      $expectedSignature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $secret, true);

      // Use hash_equals for timing-attack safety
      if (!hash_equals($signature, $expectedSignature)) {
        $response = $response->withStatus(403);
        throw new UnauthorizedException('Unauthorized access');
      }
    }
    return $response;
  }
  private function base64UrlDecode(string $data)
  {
    $base64 = strtr($data, '-_', '+/');
    $base64Padded = str_pad($base64, strlen($base64) % 4, '=', STR_PAD_RIGHT);
    return base64_decode($base64Padded);
  }
}