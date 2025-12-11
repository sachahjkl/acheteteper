<?php
// Loads and registers the logic that enables autoloading of classes.
require '../../vendor/autoload.php';

$application = Application::bootstrap();
$application->run();
