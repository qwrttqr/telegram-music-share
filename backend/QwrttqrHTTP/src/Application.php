<?php

namespace QwrttqrHTTP\src;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use QwrttqrHTTP\Attributes\QueryParam;
use QwrttqrHTTP\Attributes\Route;
use QwrttqrHTTP\Exceptions\MissingParamException;
use QwrttqrHTTP\Exceptions\RouteNotFoundException;
use QwrttqrHTTP\Helpers\MatchingRoute;
use QwrttqrHTTP\Helpers\RouteExpeditor;
use QwrttqrHTTP\Http\Uri;
use QwrttqrHTTP\Interfaces\RouteExpeditorInterface;

class Application
{
  //private Router $router;
  private array $controllers = [];
  private array $routingMap = [];
  private string|null $rootNamespace = null;
  private RouteExpeditorInterface $routeExpeditor;

  /**
   * Entry point of whole framework
   * @param $rootNamespace string - root namespace of an application
   */
  public function __construct(string $rootNamespace)
  {
    $this->routeExpeditor = new RouteExpeditor();
    $this->rootNamespace = $rootNamespace;
  }

  public function addControllerDirectory(string $path): self
  {
    $this->controllers[] = realpath($path);
    return $this;
  }

  /**
   * @throws RouteNotFoundException
   */
  public function run()
  {
    $this->discoverControllers();
    // TODO: Add middlewares and authentication handling.
    try {
      $route = $this->findMatchingRoute();
      $this->dispatch($route);
    } catch (\Exception $e) {
      $this->handleError($e);
    }
  }

  /**
   * Lookups for appropriate route in the current route map.
   * @return null|MatchingRoute - MatchingRoute instance if this route is found(with info about handling controller), null otherwise.
   * @throws RouteNotFoundException
   */
  private function findMatchingRoute(): ?MatchingRoute
  {
    $requestedMethod = $_SERVER['REQUEST_METHOD'];
    $requestUri = $_SERVER['REQUEST_URI'];
    $parsedUri = new Uri($requestUri);
    $requestedPath = $parsedUri->getPath();
    $queryString = $parsedUri->getQuery();
    foreach ($this->routingMap as $key => $routeData) {
      [$routeMethod, $routePath] = explode(':', $key, 2);
      if ($routeMethod !== $requestedMethod) {
        continue;
      }
      $pattern = $this->routeExpeditor->routeToRegexp($routePath);
      // Check if requested URI correspond to route and if that -> get param values.
      if (preg_match($pattern, $requestedPath, $matches)) {
        array_shift($matches); // Remove full path, keep only captured groups
        // Get path params from key from route map
        $paramNames = $this->routeExpeditor->extractPathParamNames($routePath);
        // Matches contains actual param values
        $pathParams = array_combine($paramNames, $matches);
        $queryParams = $this->routeExpeditor->extractQueryParams($queryString);
        return new MatchingRoute($routeData['class'], $routeData['method'], $pathParams, $queryParams);
      }
    }
    throw new RouteNotFoundException("Route not found [$requestUri}");
  }

  /**
   * @throws \ReflectionException
   */
  private function dispatch(MatchingRoute $route)
  {
    $className = $route->class;
    $methodName = $route->method;
    $pathParams = $route->pathParams;
    $queryParams = $route->queryParams;

    $controller = $this->instantiateController($className);

    $reflectionMethod = new \ReflectionMethod($controller, $methodName);

    $args = $this->resolveMethodArguments($reflectionMethod, $pathParams, $queryParams);
    $reflectionMethod->invokeArgs($controller, $args);
  }

  private function instantiateController(string $className)
  {
    $reflectionClass = new \ReflectionClass($className);
    // TODO: Add support of constructor args
    $controller = new $className();
    if ($controller instanceof \QwrttqrHTTP\Wrappers\ControllerWrapper) {
      $request = $this->createPsr7Request();
      $response = $this->createPsr7Response();
      $controller->setRequest($request);
      $controller->setResponse($response);
    }

    return $controller;
  }

  private function resolveMethodArguments(\ReflectionMethod $method, array $pathParams, array $queryParams): array
  {
    $args = [];
    $methodParams = $method->getParameters();
    // Get base params and query params

    foreach ($methodParams as $param) {
      $paramName = $param->getName();
      $paramType = $param->getType();
      $queryParamAttributes = $param->getAttributes(QueryParam::class);
      // Get values for attributes separately
      if (sizeof($queryParamAttributes) > 0) {
        if (isset($queryParams[$paramName])) {
          $args[] = $this->castParamValue($queryParams[$paramName], $paramType);
          continue;
        } else if ($param->isDefaultValueAvailable()) {
          $args[] = $param->getDefaultValue();
          continue;
        } else {
          // If no param set - throw exception
          throw new MissingParamException("Missing param $paramName");
        }
      }
      if (isset($pathParams[$paramName])) {
        $args[] = $this->castParamValue($pathParams[$paramName], $paramType);
        continue;
      }

      if ($param->isDefaultValueAvailable()) {
        $args[] = $param->getDefaultValue();
        continue;
      }

      throw new \RuntimeException(
        "Cannot resolve parameter '$paramName' for method {$method->getName()}"
      );
    }

    return $args;
  }

  private function castParamValue(string $value, ?\ReflectionType $type): mixed
  {
    if ($type === null || !$type instanceof \ReflectionNamedType) {
      return $value;
    }

    return match ($type->getName()) {
      'int' => (int)$value,
      'float' => (float)$value,
      'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
      'string' => $value,
      default => $value
    };
  }

  private function discoverControllers(): void
  {
    foreach ($this->controllers as $controller) {
      // Will look up for all .php in given controller directories.
      $files = glob($controller . '/*.php');
      foreach ($files as $file) {
        require_once $file;
        $className = $this->pathToClassName($file);
        // Make reflecton of controller class
        $reflection = new \ReflectionClass($className);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
          // Get route attributes
          $attributes = $method->getAttributes(Route::class);
          foreach ($attributes as $attribute) {
            /** @var Route $route */
            $route = $attribute->newInstance();

            $key = strtoupper($route->method) . ':' . $route->path;
            $this->routingMap[$key] = [
              'class' => $className,
              'method' => $method->getName(),
              'path' => $route->path,
              'httpMethod' => strtoupper($route->method)
            ];
          }
        }
      }
    }
  }

  /**
   * Returns fully qualified className.
   * @param $file string - path to file
   * @return string
   */
  private function pathToClassName(string $file): string
  {
    // Replaces /var/www/html/src/Controllers/MyController/File.php into Controllers/MyController.
    $relativePath = str_replace([PROJECT_ROOT . '/src/', '.php'], '', $file);

    return "$this->rootNamespace\\" . str_replace('/', '\\', $relativePath);
  }

  private function createPsr7Request(): RequestInterface
  {
    return \QwrttqrHTTP\Http\Request::createFromGlobals();
  }

  private function createPsr7Response(): ResponseInterface
  {
    return new \QwrttqrHTTP\Http\Response();
  }

  private function sendResponse(ResponseInterface $response): void
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

  private function handleError(\Exception $exception)
  {
    $response = $this->createPsr7Response();
    switch (true) {
      case $exception instanceof RouteNotFoundException:
        $response = $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        $body = json_encode([
          'error' => 'Not found',
          'message' => $exception->getMessage(),
          'status' => 404
        ]);
        $response->getBody()->write($body);
        $this->sendResponse($response);
    }
  }
}