<?php

namespace QwrttqrHTTP\Interfaces;

interface RouteExpeditorInterface
{
  /**
   * Converts given route into regexp for finding regexp matching.
   * @param string $route
   * @return mixed
   */
  public function routeToRegexp(string $route): string;

  /**
   * Extract param names from route.
   * @param string $route
   * @return array
   */
  public function extractParamNames(string $route): array;
}