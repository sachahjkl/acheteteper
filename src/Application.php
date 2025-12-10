<?php

// namespace Application;

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
    ErrorsDemoController,
    DBDemoController
};

use Acheteteper\ConfigBuilder;
use Acheteteper\Engine;

class Application
{

    private function __construct(
        private Engine $engine
    ) {}

    public static function bootstrap(): Application
    {
        $configBuilder = new ConfigBuilder();
        $configBuilder->setViewDir(__DIR__ . '/views');
        $configBuilder->setDbPath(__DIR__ . '/../database.db');
        $configBuilder->enableDebug();
        $config = $configBuilder->build();

        $engine = new Engine($config);

        $engine->registerDatasource('default', \Acheteteper\SqliteDataSource::class);
        $engine->registerService(\Services\DbDemoService::class);
        $engine->registerRepository(\Repositories\DbDemoRepository::class);

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
        $engine->registerController('/db', DBDemoController::class);

        return new Application($engine);
    }

    public function run(): void
    {
        $this->engine->run();
    }
}
