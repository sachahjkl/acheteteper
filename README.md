# Minimal PHP framework (Acheteteper)

## Getting started
### Nix

```bash
nix run
```

The application listens on http://localhost:8000.

Run all checks with:

```bash
nix flake check
```

### OCI image

```bash
nix build .#dockerImage
podman load --input result
podman run --rm -p 8000:8000 -v acheteteper-data:/data acheteteper:1.0.0
```

The image stores the SQLite database and uploads in `/data`.

### Configuration

`config/app.php` returns a `Config` object built with `ConfigBuilder`.

Set `APP_CONFIG` to load another PHP file.

The main variables are `DB_PATH`, `UPLOADS_PATH`, `DEBUG`, `PUBLIC_URL`, and `TRUSTED_PROXIES`.

### Legacy development server
```bash
build serve              # 127.0.0.1:8000
build serve 8080         # port custom
build serve 0.0.0.0 8080 # host + port custom
```

/!\ Warning: it is EXTREEEEMELY slow (adds 300ms to every request).

## Usage

### Entry point

Create an `index.php` file in the public directory:

```php
<?php
require '../../vendor/autoload.php';

use controllers\IndexController;
use Acheteteper\ConfigBuilder;
use Acheteteper\Engine;
use Acheteteper\SqliteDataSource;
use Services\DbDemoService;
use Repositories\DbDemoRepository;

$configBuilder = new ConfigBuilder();
$configBuilder->setViewDir(__DIR__ . '/../views');
$configBuilder->setDbPath(__DIR__ . '/../database.db');
$config = $configBuilder->build();

$engine = new Engine($config);
$engine->registerDatasource('default', SqliteDataSource::class);
$engine->registerService(DbDemoService::class);
$engine->registerRepository(DbDemoRepository::class);

$engine->registerController('/', IndexController::class);

$engine->run();
```

### Create a controller

Controllers extend `ControllerBase`:

```php
<?php

namespace controllers;

use Acheteteper\ControllerBase;

class IndexController extends ControllerBase
{
    public function index()
    {
        return $this->render('index', [
            'name' => 'Sacha',
            'items' => ['Item 1', 'Item 2']
        ]);
    }
}
```

### Routes

Routes follow the `/controller/action` pattern:

- `/` → `IndexController::index()`
- `/about` → `AboutController::index()`
- `/about/contact` → `AboutController::contact()`

Register routes with `registerController()`:

```php
$engine->registerController('/', IndexController::class);
$engine->registerController('/about', AboutController::class);
```

Each public action declared in the controller must return a `Response`.

### Controller methods

- `render(string $view, array $data = []): Response` - Renders a view with data
- `redirect(string $url): Response` - Redirects to a URL
- `json(array $data): Response` - Returns a JSON response
- `getFieldValue(string $key)` - Gets a POST/GET value
- `getFieldsValues(array $keys)` - Gets multiple POST/GET values
- `datasource(string $name = 'default')` - Gets a datasource
- `getService(string $class)` - Gets a service
- `getRepository(string $class)` - Gets a repository
- `fail(int $status, string $message)` - Throws an HttpException

### Views

Views are PHP files in the configured directory (`viewDir`). Supported extensions are `.phtml`, `.php`, and `.html`.

```php
<h1>Page</h1>
<p>Bonjour <?= $name; ?></p>
```

### Datasource / Services / Repositories

- Declare a datasource: `$engine->registerDatasource('default', SqliteDataSource::class);`
- Declare a service: `$engine->registerService(MyService::class);`
- Declare a repository: `$engine->registerRepository(MyRepository::class);`
- In a controller: `$this->datasource()` or `$this->getService(MyService::class)` or `$this->getRepository(MyRepository::class)`

Demo example: `/db` (DbDemoController) uses SQLite, a service, and a repository for basic CRUD operations on `demo_items`.

### Assets / favicon
- Favicon: `src/public/logo.png`
- Footer badges: `src/public/php-power-micro.png` and images in `src/public/footer/`

### Uploads and static files
- `DB_PATH` and `UPLOADS_PATH` can be set through the environment.
- Uploads are served as static files through `/uploads` (see `Application::bootstrap()` and `Engine::registerStaticDir`).

### UI
- Tailwind components (buttons, inputs, select) are available in `src/Components.php`.

## Inspirations

- https://gregwar.com/php/components.html
- https://symfony.com/ (Cocorico)

## Useful links

- https://www.php-fig.org/psr/psr-4/
- https://www.slimframework.com/docs/v3/tutorial/first-app.html
