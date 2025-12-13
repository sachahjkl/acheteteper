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

use Acheteteper\ConfigBuilder;
use Acheteteper\Engine;
use Repositories\DbDemoRepository;
use Services\DbDemoService;
use Utils\Utils;

class Application
{

    private function __construct(
        private Engine $engine
    ) {}


    public static function loadConfig(): array
    {
        $dbFromEnv = getenv('DB_PATH');
        $uploadsFromEnv = getenv('UPLOADS_PATH');
        if ($dbFromEnv) {
            $dbPath = $dbFromEnv;
        } else {
            $dbPath = __DIR__ . '/../data/database.db';
        }
        if ($uploadsFromEnv) {
            $uploadsPath = $uploadsFromEnv;
        } else {
            $uploadsPath = __DIR__ . '/../data/uploads';
        }
        $debug = getenv('DEBUG');
        if ($debug === 'true') {
            $debug = true;
        } else {
            $debug = false;
        }

        return [
            'dbPath' => $dbPath,
            'uploadsPath' => $uploadsPath,
            'debug' => $debug
        ];
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
        self::ensureUploadsDir($config['uploadsPath']);

        $configBuilder = new ConfigBuilder();
        $configBuilder->setViewDir(__DIR__ . '/Views');
        $configBuilder->setDbPath($config['dbPath']);
        $configBuilder->setUserConfig('uploadsPath', $config['uploadsPath']);

        $configDebug = $config['debug'];
        $configBuilder->setDebugResolver(function () use ($configDebug): bool {
            return Utils::debugFromSession() ?? $configDebug;
        });

        $config = $configBuilder->build();

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
