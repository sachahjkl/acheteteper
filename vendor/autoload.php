<?php


$root = dirname(__DIR__);

spl_autoload_register(static function (string $class) use ($root): void {
    $class = ltrim($class, '\\');
    if (str_starts_with($class, 'Acheteteper\\')) {
        $path = $root . '/lib/' . str_replace('\\', '/', $class) . '.php';
    } else {
        $path = $root . '/src/' . str_replace('\\', '/', $class) . '.php';
    }

    $resolved = realpath($path);
    if ($resolved !== false && is_file($resolved)) {
        require $resolved;
    }
});
