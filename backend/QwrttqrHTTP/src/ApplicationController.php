<?php

namespace QwrttqrHTTP\src;

use QwrttqrHTTP\Attributes\Route;
use QwrttqrHTTP\Exceptions\MissingParamException;
use QwrttqrHTTP\Exceptions\RouteNotFoundException;
use QwrttqrHTTP\Helpers\HttpBroker;
use QwrttqrHTTP\Helpers\MatchingRoute;
use QwrttqrHTTP\Helpers\RouteExpeditor;
use QwrttqrHTTP\Http\Uri;
use QwrttqrHTTP\Interfaces\HttpBrokerInterface;
use QwrttqrHTTP\Interfaces\MiddlewareInterface;
use QwrttqrHTTP\Interfaces\RouteExpeditorInterface;
use QwrttqrHTTP\Middlewares\MiddlewareHandler;
use ReflectionException;
use RuntimeException;

class ApplicationController
{
  //private Router $router;
  private array $controllers = [];
  private array $middlewares = [];
  private array $routingMap = [];
  private string|null $rootNamespace;
  private RouteExpeditorInterface $routeExpeditor;
  private HttpBrokerInterface $httpBroker;
  private MiddlewareHandler $middlewareHandler;

  /**
   * Entry point of whole framework
   * @param $rootNamespace string - root namespace of an application
   */
  public function __construct(string $rootNamespace)
  {
    $this->routeExpeditor = RouteExpeditor::getInstance();
    $this->httpBroker = HttpBroker::getInstance();
    $this->rootNamespace = $rootNamespace;
    $this->middlewareHandler =new MiddlewareHandler();
  }

  public function addControllerDirectory(string $path): self
  {
    $this->controllers[] = realpath($path);
    return $this;
  }

  public function addMiddlewares(array $middlewares): void
  {
    foreach ($middlewares as $middleware) {
      if (!$middleware instanceof MiddlewareInterface) {
        throw new \InvalidArgumentException(
          "All middlewares must implement " . MiddlewareInterface::class
        );
      }
    }

    $this->middlewareHandler->addMiddlewares($middlewares);
  }
  public function run(): void
  {
    $request = $this->httpBroker->createPsr7Request();
    $response = $this->httpBroker->createPsr7Response();

    $this->discoverControllers();
    try {
      $route = $this->findMatchingRoute();
      $finalHandler = function ($request, $response) use ($route) {
        return $this->dispatch($route);
      };
      $finalResponse = $this->middlewareHandler->handle($route, $request, $response, $finalHandler);
      $this->httpBroker->sendResponse($finalResponse);
    } catch (\Exception $e) {
      $this->handleError($e);
    }
  }

  /**
   * Lookups for appropriate route in the current route map.
   * @return MatchingRoute - MatchingRoute instance if this route is found(with info about handling controller), null otherwise.
   * @throws RouteNotFoundException
   */
  private function findMatchingRoute(): MatchingRoute
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
   * @throws ReflectionException
   * @throws MissingParamException
   */
  private function dispatch(MatchingRoute $route): \Psr\Http\Message\ResponseInterface
  {
    $className = $route->class;
    $methodName = $route->method;
    $pathParams = $route->pathParams;
    $queryParams = $route->queryParams;

    $controller = $this->instantiateController($className);

    $reflectionMethod = new \ReflectionMethod($controller, $methodName);

    $args = $this->resolveMethodArguments($reflectionMethod, $pathParams, $queryParams);

    // Start output buffering
    ob_start();

    try {
      $result = $reflectionMethod->invokeArgs($controller, $args);

      // If the method returned a response, use it
      if ($result instanceof \Psr\Http\Message\ResponseInterface) {
        $response = $result;
      }
      // Otherwise, get the buffered output
      $output = ob_get_clean();
      $response = $this->httpBroker->createPsr7Response();
      $response->getBody()->write($output);
      return $response;

    } catch (\Exception $e) {
      ob_end_clean();
      throw $e;
    }
  }

  /**
   * @throws MissingParamException
   */
  private function resolveMethodArguments(\ReflectionMethod $method, array $pathParams, array $queryParams): array
  {
    $args = [];
    $methodParams = $method->getParameters();
    $actualParams = array_merge($pathParams, $queryParams);
    foreach ($methodParams as $param) {
      $this->resolveArgsToTypes($args, $param, $method, $actualParams);
    }

    return $args;
  }

  /**
   * @param array $args - Arguments array
   * @param \ReflectionParameter $param
   * @param \ReflectionMethod $method
   * @param array $actualParams
   * @return void
   * @throws MissingParamException
   */
  private function resolveArgsToTypes(array &$args, \ReflectionParameter $param, \ReflectionMethod $method, array $actualParams): void
  {
    $paramName = $param->getName();
    $paramType = $param->getType();
    if (isset($actualParams[$paramName])) {
      $args[] = $this->castParamValue($actualParams[$paramName], $paramType);
    } else if ($param->isDefaultValueAvailable()) {
      $args[] = $param->getDefaultValue();
    } else if (!$param->allowsNull()) {
      // If no param set - throw exception
      throw new MissingParamException("Missing param $paramName");
    } else {
      throw new RuntimeException("Cannot resolve parameter $param->name in method $method->name");
    }

  }

  private function instantiateController(string $className)
  {
    // TODO: Add support of constructor args
    $request = $this->httpBroker->createPsr7Request();
    $response = $this->httpBroker->createPsr7Response();
    return new $className($request, $response);
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

  private function handleError(\Exception $exception): void
  {
    $response = $this->httpBroker->createPsr7Response()->withHeader('Content-Type', 'application/json');
    switch (true) {
      case $exception instanceof RouteNotFoundException:
        $response = $response->withStatus(404);
        $body = json_encode([
          'error' => 'Not found',
          'message' => $exception->getMessage(),
        ]);
        $response->getBody()->write($body);
        break;
      case $exception instanceof MissingParamException:
        $response = $response->withStatus(400);
        $body = json_encode([
          'error' => 'Missing param',
          'message' => $exception->getMessage(),
        ]);
        $response->getBody()->write($body);
        break;
      default:
        $response = $response->withStatus(500);
        $body = json_encode([
          'error' => 'Server error',
          'message' => "Some error occurred on server side, please, contact us"
        ]);
        $response->getBody()->write($body);
        break;
    }
    $this->httpBroker->sendResponse($response);
  }
}