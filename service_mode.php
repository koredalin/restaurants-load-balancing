<!DOCTYPE html>

<html>
  <head>
    <title>Food Drivers</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
    <h1>Food Drivers</h1>
    <?php
    require 'vendor/autoload.php';
    
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    use Drivers\Models\DriverBalancingSimulation;

    $config = require_once './src/config/config.php';

    $system = new DriverBalancingSimulation($config);
    $system->CreateRandomFreeDrivers();
    $system->RandomizedLoad();
    echo '<pre>';
    echo '<h3>Initial restaurants load.</h3>'.PHP_EOL;
    print_r($system->getLoadByRestaurants());
    echo '<h3>Rrestaurants until estimations.</h3>'.PHP_EOL;
    $system->CalculateBalance();
    echo '<h3>Final Rrestaurants load.</h3>'.PHP_EOL;
    print_r($system->getLoadByRestaurants());
    echo '<h3>Drivers Transfers.</h3>'.PHP_EOL;
    print_r($system->getDriverTransfers());
    echo '<h3>Drivers Transfers Set Back.</h3>'.PHP_EOL;
    print_r($system->getDriverTransfersSetBack());
//    print_r($system->getRestaurants());
    echo '</pre>';
    ?>
  </body>
</html>
