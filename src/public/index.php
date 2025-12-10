<?php
// Loads and registers the logic that enables autoloading of classes.
require '../../vendor/autoload.php';

use Controllers\{
    IndexController,
    RequestDemoController,
    ResponseDemoController,
    ValidationDemoController,
    SessionDemoController,
    FlashDemoController,
    CsrfDemoController,
    UploadDemoController,
    ApiDemoController,
    HelpersDemoController,
    RoutingDemoController,
    ErrorsDemoController
};
use Httpeur\ConfigBuilder;
use Httpeur\Engine;

$configBuilder = new ConfigBuilder();
$configBuilder->setViewDir(__DIR__ . '/../views');
$config = $configBuilder->build();

$engine = new Engine($config);
$engine->registerController('/', IndexController::class);
$engine->registerController('/request', RequestDemoController::class);
$engine->registerController('/response', ResponseDemoController::class);
$engine->registerController('/validation', ValidationDemoController::class);
$engine->registerController('/session', SessionDemoController::class);
$engine->registerController('/flash', FlashDemoController::class);
$engine->registerController('/csrf', CsrfDemoController::class);
$engine->registerController('/upload', UploadDemoController::class);
$engine->registerController('/api', ApiDemoController::class);
$engine->registerController('/helpers', HelpersDemoController::class);
$engine->registerController('/routing', RoutingDemoController::class);
$engine->registerController('/errors', ErrorsDemoController::class);

$engine->run();
