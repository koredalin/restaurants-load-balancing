<?php

namespace Drivers\Models;

use Drivers\Helpers\Location;
use Drivers\Models\Interfaces\DriverBalancingSimulationInterface;

/**
 * Description of DriverBalancingSimulation
 *
 * @author H1
 */
class DriverBalancingSimulation implements DriverBalancingSimulationInterface
{
    private array $restaurants;
    private array $driverTransfers;
    private int $maxTransferBlocks = 5;

    public function __construct(
        private array $config
    ) {}

    public function getDriverTransfers(): array
    {
        return $this->driverTransfers;
    }

    public function CreateRandomFreeDrivers(): void
    {
        $this->createRestaurants();
    }

    public function RandomizedLoad(): void
    {
        foreach ($this->restaurants as $restaurant) {
            $restaurant->createOrders();
            $restaurant->calculateLoad();
        }
    }

    /**
     * TODO Find the restaurant with the biggest load.
     * TODO Find a driver to max 6000 m on a restaurant with minimal load.
     * TODO Find the nearest driver from the second restaurant.
     * TODO If the second restaurant has big load.
     * Continue the same practice max 2 more times. Total max of 3 driver transfers.
     *
     * @return void
     */
    public function CalculateBalance(): void
    {
        $this->driverTransfers = [];
        $this->calculateBalanceSingleNeedTransfers();
        foreach ($this->driverTransfers as $key => $transferGroup) {
            if (empty($transferGroup)) {
                unset($this->driverTransfers[$key]);
            }
        }
        $this->driverTransfers = array_values($this->driverTransfers);
    }

    private function calculateBalanceSingleNeedTransfers(): void
    {
        echo '<pre>';
        $restaurantTransferTo = $this->getHighestLoadRestaurant();
        if (!$restaurantTransferTo) {
            // No restaurants that need transfers.
            return;
        }
        
        $this->driverTransfers[] = [];
        for ($ii = 0; $ii < $this->config['maxLoadCascades']; $ii++) {
        var_dump('$restaurantTransferTo: ', $restaurantTransferTo->getId());
echo PHP_EOL;
var_dump('$restaurantTransferToLoad: ', $restaurantTransferTo->currentLoad);
            $nearestDriver = $this->getNearestLowestLoadRestaurantDriver($restaurantTransferTo);
            var_dump('$nearestDriver: ', $nearestDriver ? $nearestDriver->getId() : 'null');
            if (!$nearestDriver) {
                $restaurantTransferTo->farAway = true;
                break;
            } else {
//                if (empty($this->driverTransfers) || !empty(end($this->driverTransfers))) {
//                    $this->driverTransfers[] = [];
//                }

                $nextRestaurantTransferTo = $this->exchangeDriver($restaurantTransferTo, $nearestDriver);
                if ($nextRestaurantTransferTo) {
                    $restaurantTransferTo = $nextRestaurantTransferTo;
                } else {
            var_dump('$nextRestaurantTransferTo: null');
                    break;
                }
//        print_r($restaurantTransferTo);
//        exit;
            }

            // TODO Need investigation.
        echo '</pre>';
        }
        
        if ($this->maxTransferBlocks <= 5) {
            $this->calculateBalanceSingleNeedTransfers();
            $this->maxTransferBlocks++;
        }
    }
    
    public function getRestaurants(): array
    {
        return $this->restaurants;
    }
    
    public function getLoadsByRestaurant(): array
    {
        $result = [];
        foreach ($this->restaurants as $restaurant) {
            $result[] = [$restaurant->getId(), $restaurant->currentLoad];
        }
        
        return $result;
    }
    
    private function createRestaurants(): void
    {
        if (empty($this->config['restaurants'])) {
            throw new Exception('No restaurants set in the config.');
        }

        $this->restaurants = [];
        $nextDriverId = 0;
        foreach ($this->config['restaurants'] as $restaurantKey => $restaurantArr) {
            $restaurant = new Restaurant($this->config, $this->config['restaurants'][$restaurantKey][0], $nextDriverId);
            $this->restaurants[] = $restaurant;
            $drivers = $restaurant->getDrivers();
            $lastDriver = !empty($drivers) ? end($drivers) : null;
            $nextDriverId = $lastDriver ? $lastDriver->getId() + 1 : $nextDriverId + 1;
        }
    }

    private function getHighestLoadRestaurant(): ?Restaurant
    {
        $biggestLoad = -1;
        $restaurantKey = null;
        foreach ($this->restaurants as $key => $restaurant) {
            if ($restaurant->farAway) {
                continue;
            }

            if ($restaurant->currentLoad > $biggestLoad) {
                $biggestLoad = $restaurant->currentLoad;
                $restaurantKey = $key;
            }
        }

        if ($restaurantKey === null) {
            return null;
        }

        return $this->restaurants[$restaurantKey];
    }

    private function getNearestLowestLoadRestaurantDriver(Restaurant $restaurant): ?Driver
    {
        $result = null;
        $load = 21;
        foreach ($this->restaurants as $key => $neighborRestaurant) {
            // Skip the restaurant for which we are searching drivers.
            if ($neighborRestaurant->getId() === $restaurant->getId()) {
                continue;
            }

            foreach ($neighborRestaurant->getDrivers() as $driverKey => $driver) {
                if (
                    $neighborRestaurant->currentLoad >= $load
                    || $driver->isTransferred
                ) {
                    continue;
                }

                $distanceBetweenRestaurantAndDriver = Location::calculateDistance(
                    $restaurant->getLat(),
                    $restaurant->getLng(),
                    $driver->lat,
                    $driver->lng
                );
                if ($distanceBetweenRestaurantAndDriver <= $this->config['driverMaxTransferDistanceInMeters']) {
                    $load = $neighborRestaurant->currentLoad;
                    $result = $driver;
                }
            }
        }
        
        return $result;
    }

    /**
     * Transfers a driver to the next restaurant.
     * Returns the restaurant that make a transfer or null if the restaurant has negative load balance. 
     *
     * @param Restaurant $restaurantInNeed
     * @param Driver $driver
     * @return Restaurant|null
     * @throws ApplicationException
     */
    private function exchangeDriver(Restaurant $restaurantInNeed, Driver $driver): ?Restaurant
    {
        foreach ($this->restaurants as $key => $restaurant) {
//            var_dump($this->restaurants[$key]);
//            var_dump($restaurant);
//            exit;
            if ($driver->restaurantId === $restaurant->getId()) {
                $restaurant->removeDriver($driver->getId());
                $restaurantInNeed->addDriver($driver);
//                $driver->lat = $restaurantInNeed->getLat();
//                $driver->lng = $restaurantInNeed->getLng();
                $restaurant->currentLoad++;
                $restaurantInNeed->currentLoad--;
                $this->driverTransfers[array_key_last($this->driverTransfers)][] = [
                    'rId' => $restaurant->getId(),
                    'rLoad' => $restaurant->currentLoad,
                    'dId' => $driver->getId(),
                    'transferredToRId' => $restaurantInNeed->getId(),
                    'transferredToRLoad' => $restaurantInNeed->currentLoad,
                ];

                if ($restaurant->currentLoad < 0) {
                    return null;
                } else {
        
//        var_dump($restaurant);
                    return $restaurant;
                }
            }
        }
        
        throw new ApplicationException('Driver not moved.');
    }
}
