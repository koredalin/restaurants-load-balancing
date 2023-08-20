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
    print_r($system->getLoadByRestaurants());
    $system->CalculateBalance();
    print_r($system->getDriverTransfers());
//    print_r($system->getLoadsByRestaurant());
//    print_r($system->getRestaurants());
    echo '</pre>';
    ?>
  </body>
</html>
