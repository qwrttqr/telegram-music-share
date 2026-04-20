<?php

namespace Framework\src;

class Application
{
  //private Router $router;
  private array $controllers = [];

  public function __construct()
  {
//    $this->router = new Router();
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

        $reflection = new \ReflectionClass($className);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
          $attributes = $method->getAttributes(Route::class);
          foreach ($attributes as $attribute) {
            $route = $attribute->newInstance();

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

    return 'App\\' . str_replace('/', '\\', $relativePath);
  }
}