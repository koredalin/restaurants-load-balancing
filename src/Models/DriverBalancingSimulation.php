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

    public function __construct(
        private array $config
    ) {}

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
        $highestLoadRestaurant = $this->getHighestLoadRestaurant();
        if (!$highestLoadRestaurant) {
            // No restaurants that need transfers.
            return;
        }

        $nearestDriver = $this->getNearestLowestLoadRestaurantDriver($highestLoadRestaurant);
        if (!$nearestDriver) {
            $highestLoadRestaurant->farAway = true;
        } else {
            $this->exchangeDriver($highestLoadRestaurant, $nearestDriver);
        }

        $this->CalculateBalance();
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
                    count($neighborRestaurant->getDrivers()) === 0
                    || $neighborRestaurant->currentLoad >= $load
                    || $driver->isTransferred
                ) {
                    continue;
                }

                $distanceBetweenRestaurantAndDriver = Location::calculateDistance(
                    $restaurant->getLat(),
                    $restaurant->getLng(),
                    $driver->getInitialLat(),
                    $driver->getInitialLng()
                );
                if ($distanceBetweenRestaurantAndDriver <= $this->config['driverMaxTransferDistanceInMeters']) {
                    $load = $neighborRestaurant->currentLoad;
                    $result = $driver;
                }
            }
        }
        
        return $result;
    }

    private function exchangeDriver(Restaurant $restaurantInNeed, Driver $driver): void
    {
        foreach ($this->restaurants as $restaurant) {
            if ($driver->getInitialRestaurantId() === $restaurant->getId()) {
                $restaurant->removeDriver($driver->getId());
                $restaurantInNeed->addDriver($driver);
                $restaurant->currentLoad++;
                $restaurantInNeed->currentLoad--;
                return;
            }
        }
        
        throw new ApplicationException('Driver not moved.');
    }
}
