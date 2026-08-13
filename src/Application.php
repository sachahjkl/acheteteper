<?php

use controllers\{
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
    DBDemoController,
    ConfigController
};

use Acheteteper\Config;
use Acheteteper\Engine;
use repositories\DbDemoRepository;
use services\DbDemoService;

class Application
{

    private function __construct(
        private Engine $engine
    ) {}


    public static function loadConfig(): Config
    {
        $path = getenv('APP_CONFIG') ?: __DIR__ . '/../config/app.php';
        $config = require $path;
        if (!$config instanceof Config) {
            throw new \RuntimeException("Config file must return a Config: $path");
        }
        return $config;
    }

    public static function ensureUploadsDir(string $uploadsPath): void
    {
        if (!is_dir($uploadsPath)) {
            mkdir($uploadsPath, 0777, true);
        }
    }

    public static function bootstrap(): Application
    {

        $config = self::loadConfig();
        self::ensureUploadsDir($config->getUserConfig('uploadsPath'));

        $engine = new Engine($config);

        $engine->registerDatasource('default', \Acheteteper\SqliteDataSource::class);
        $engine->registerService(DbDemoService::class);
        $engine->registerRepository(DbDemoRepository::class);

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
        $engine->registerController('/config', ConfigController::class);

        $engine->registerStaticDir('/uploads', $config->getUserConfig('uploadsPath'));

        return new Application($engine);
    }

    public function run(): void
    {
        $this->engine->run();
    }
}
