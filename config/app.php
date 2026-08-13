<?php

use Acheteteper\ConfigBuilder;
use utils\Utils;

$root = dirname(__DIR__);
$builder = new ConfigBuilder();

return $builder
    ->setViewDir($root . '/src/views')
    ->setDbPath(getenv('DB_PATH') ?: $root . '/data/database.db')
    ->setUserConfig('uploadsPath', getenv('UPLOADS_PATH') ?: $root . '/data/uploads')
    ->setDebugResolver(function (): bool {
        return Utils::debugFromSession() ?? getenv('DEBUG') === 'true';
    })
    ->enableCsrfProtection()
    ->setPublicUrl(getenv('PUBLIC_URL') ?: null)
    ->setTrustedProxies(array_filter(explode(',', getenv('TRUSTED_PROXIES') ?: '')))
    ->setMaxJsonBodyBytes(1024 * 1024)
    ->build();
