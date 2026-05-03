<?php

namespace QwrttqrHTTP\src;

use QwrttqrHTTP\Http\Uri;
use QwrttqrHTTP\Interfaces\RouteExpeditorInterface;
use QwrttqrHTTP\Helpers\RouteExpeditor;
use QwrttqrHTTP\Exceptions\RouteNotFoundException;
use QwrttqrHTTP\Helpers\MatchingRoute;

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
    $route = $this->findMatchingRoute();
    $this->dispatch($route);
  }

  /**
   * @throws \ReflectionException
   */
  private function dispatch(MatchingRoute $route)
  {
    $className = $route->class;
    $methodName = $route->method;
    $params = $route->params;

    $controller = $this->instantiateController($className);

    $reflectionMethod = new \ReflectionMethod($controller, $methodName);

    $args = $this->resolveMethodArguments($reflectionMethod, $params);
    $reflectionMethod->invokeArgs($controller, $args);
  }

  private function instantiateController(string $className)
  {
    $reflectionClass = new \ReflectionClass($className);
    // TODO: Add support of constructor args
    return new $className();
  }

  private function resolveMethodArguments(\ReflectionMethod $method, array $routeParams)
  {
    $args = [];
    $methodParams = $method->getParameters();
    foreach ($methodParams as $param) {
      $paramName = $param->getName();
      $paramType = $param->getType();

      if (isset($routeParams[$paramName])) {
        $args[] = $this->castParamValue($routeParams[$paramName], $paramType);
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
    foreach ($this->routingMap as $key => $routeData) {
      [$routeMethod, $routePath] = explode(':', $key, 2);
      if ($routeMethod !== $requestedMethod) {
        continue;
      }
      $pattern = $this->routeExpeditor->routeToRegexp($routePath);
      if (preg_match($pattern, $requestedPath, $matches)) {
        array_shift($matches); // Remove full path, keep only captured groups

        $paramNames = $this->routeExpeditor->extractParamNames($routePath);
        // Matches contains actual param values
        $params = array_combine($paramNames, $matches);

        return new MatchingRoute($routeData['class'], $routeData['method'], $params);
      }
    }
    throw new RouteNotFoundException("Route not found [$requestUri}");
  }
}