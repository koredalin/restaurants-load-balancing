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

    use Drivers\Models\DriverBalancingSimulation;

    $config = require_once './src/config/config.php';

    $system = new DriverBalancingSimulation($config);
    $system->CreateRandomFreeDrivers();
    $system->RandomizedLoad();
    echo '<pre>';
    echo '<h3>Initial restaurants load.</h3>'.PHP_EOL;
    print_r($system->getLoadByRestaurants());
    echo '<h3>Rrestaurants load after estimations.</h3>'.PHP_EOL;
    $system->CalculateBalance();
    echo '<h3>Drivers Transfers.</h3>'.PHP_EOL;
    print_r($system->getDriverTransfers());
    echo '<h3>Rrestaurants load after estimations.</h3>'.PHP_EOL;
    print_r($system->getLoadByRestaurants());
//    print_r($system->getRestaurants());
    echo '</pre>';
    ?>
  </body>
</html>
