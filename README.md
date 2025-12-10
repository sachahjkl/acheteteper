# Framework PHP minimal (Acheteteper)

## Usage

### Point d'entrée

Créer un fichier `index.php` dans le répertoire public :

```php
<?php
require '../../vendor/autoload.php';

use Controllers\IndexController;
use Acheteteper\ConfigBuilder;
use Acheteteper\Engine;
use Acheteteper\SqliteDataSource;
use Services\DbDemoService;
use Repositories\DbDemoRepository;

$configBuilder = new ConfigBuilder();
$configBuilder->setViewDir(__DIR__ . '/../views');
$configBuilder->setDbPath(__DIR__ . '/../database.sqlite');
$config = $configBuilder->build();

$engine = new Engine($config);
$engine->registerDatasource('default', SqliteDataSource::class);
$engine->registerService(DbDemoService::class);
$engine->registerRepository(DbDemoRepository::class);

$engine->registerController('/', IndexController::class);

$engine->run();
```

### Créer un contrôleur

Les contrôleurs étendent `ControllerBase` :

```php
<?php

namespace Controllers;

use Acheteteper\ControllerBase;

class IndexController extends ControllerBase
{
    public function index()
    {
        $this->render('index', [
            'name' => 'Sacha',
            'items' => ['Item 1', 'Item 2']
        ]);
    }
}
```

### Routes

Les routes suivent le pattern `/controller/action` :

- `/` → `IndexController::index()`
- `/about` → `AboutController::index()`
- `/about/contact` → `AboutController::contact()`

Enregistrer les routes avec `registerController()` :

```php
$engine->registerController('/', IndexController::class);
$engine->registerController('/about', AboutController::class);
```

### Méthodes du contrôleur

- `render(string $view, array $data = [])` - Rend une vue avec des données
- `redirect(string $url)` - Redirige vers une URL
- `json(array $data)` - Retourne une réponse JSON
- `getFieldValue(string $key)` - Récupère une valeur POST/GET
- `getFieldsValues(array $keys)` - Récupère plusieurs valeurs POST/GET
- `datasource(string $name = 'default')` - Récupère un datasource
- `getService(string $class)` - Récupère un service
- `getRepository(string $class)` - Récupère un repository
- `fail(int $status, string $message)` - Lance une HttpException

### Vues

Les vues sont des fichiers PHP dans le répertoire configuré (`viewDir`). Extensions supportées : `.phtml`, `.php`, `.html`.

```php
<h1>Page</h1>
<p>Bonjour <?= $name; ?></p>
```

### Datasource / Services / Repositories

- Déclarer un datasource : `$engine->registerDatasource('default', SqliteDataSource::class);`
- Déclarer un service : `$engine->registerService(MyService::class);`
- Déclarer un repository : `$engine->registerRepository(MyRepository::class);`
- Dans un contrôleur : `$this->datasource()` ou `$this->getService(MyService::class)` ou `$this->getRepository(MyRepository::class)`

Exemple de demo : `/db` (DbDemoController) utilise SQLite, un service et un repository pour un CRUD simple sur `demo_items`.

## Démarrage

Démarrer le serveur de développement :

```bash
build serve
```

Par défaut, le serveur écoute sur `127.0.0.1:8000`. Pour changer le port :

```bash
build serve 8080
```

Pour changer l'hôte et le port :

```bash
build serve 0.0.0.0 8080
```

## Inspirations

- https://gregwar.com/php/components.html

## Liens utiles

- https://www.php-fig.org/psr/psr-4/
- https://www.slimframework.com/docs/v3/tutorial/first-app.html
