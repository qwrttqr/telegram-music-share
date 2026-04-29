<?php

namespace Framework\src;

class Application
{
  //private Router $router;
  private array $controllers = [];
  private array $routingMap = [];
  private string|null $rootNamespace = null;
  /**
   * Entry point of whole framework
   * @param $rootNamespace string - root namespace of an application
   */
  public function __construct(string $rootNamespace)
  {
    $this->rootNamespace = $rootNamespace;
  }

  public function addControllerDirectory(string $path): self
  {
    $this->controllers[] = realpath($path);
    return $this;
  }

  public function discoverControllers(): void
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
}