<?php
require_once __DIR__ . '/../Framework/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Framework\Application;

$app = new Application();

$app->addControllerDirectory(__DIR__ . '/../src/Controllers');
$app->discoverControllers();