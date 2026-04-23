<?php
define('PROJECT_ROOT', realpath(__DIR__ . '/..'));
use Framework\src\Application;

/**
 * Entry point of an application.
 * @param $appRootNamespace string - defines root namespace of entire application
 */
return function (string $appRootNamespace) {
  $app = new Application($appRootNamespace);

  $app->addControllerDirectory(__DIR__ . '/../src/Controllers');
  $app->discoverControllers();
};