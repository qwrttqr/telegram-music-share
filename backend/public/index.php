<?php
require_once __DIR__ . '/../vendor/autoload.php';
use QwrttqrHTTP\src\ApplicationController;

define('PROJECT_ROOT', realpath(__DIR__ . '/..'));

$app = new ApplicationController('App');
$app->addControllerDirectory(PROJECT_ROOT . '/src/Controllers');
$app->run();