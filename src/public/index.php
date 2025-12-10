<?php
// Loads and registers the logic that enables autoloading of classes.
require '../../vendor/autoload.php';

use Controllers\{IndexController, AboutController};
use Httpeur\ConfigBuilder;
use Httpeur\Engine;

$configBuilder = new ConfigBuilder();
$configBuilder->setViewDir(__DIR__ . '/../views');
$config = $configBuilder->build();

$engine = new Engine($config);
$engine->registerController('/', IndexController::class);
$engine->registerController('/about', AboutController::class);

$engine->run();
