
<?php
require 'vendor/autoload.php';

use Drivers\Controllers\HomeController;

$config = require_once './src/config/config.php';

$system = new HomeController($config);
echo $system->generateDriverTransfers();
