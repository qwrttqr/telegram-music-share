<?php
define('PROJECT_ROOT', realpath(__DIR__ . '/..'));
use QwrttqrHTTP\src\ApplicationController;

/**
 * Entry point of an application.
 * @param $appRootNamespace string - defines root namespace of entire application
 */
return function (string $appRootNamespace) {
  $app = new ApplicationController($appRootNamespace);

  $app->addControllerDirectory(__DIR__ . '/../src/Controllers');
  $app->run();
};