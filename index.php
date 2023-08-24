
<?php
require 'vendor/autoload.php';

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

use Drivers\Controllers\HomeController;

$config = require_once './src/config/config.php';

$system = new HomeController($config);
echo $system->generateDriverTransfers();
