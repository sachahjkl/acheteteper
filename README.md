# Tentative de "framework" simple PHP

## Usage

### Point d'entrée

Créer un fichier `index.php` dans le répertoire public :

```php
<?php
require '../../vendor/autoload.php';

use Controllers\IndexController;
use Acheteteper\ConfigBuilder;
use Acheteteper\Engine;

$configBuilder = new ConfigBuilder();
$configBuilder->setViewDir(__DIR__ . '/../views');
$config = $configBuilder->build();

$engine = new Engine($config);
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

### Vues

Les vues sont des fichiers PHP dans le répertoire configuré (`viewDir`). Extensions supportées : `.phtml`, `.php`, `.html`.

```php
<h1>Page</h1>
<p>Bonjour <?= $name; ?></p>
```

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
