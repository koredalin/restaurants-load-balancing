<?php

namespace Drivers\Models;

/**
 * Description of DriverBalancingSimulation
 *
 * @author H1
 */
class DriverBalancingSimulation
{
    private array $restaurants;

    public function __construct(
        private array $config
    ) {
        $this->createRestaurants();
    }

    public function createRandomFreeDrivers(): void
    {
        // TODO
    }

    public function randomizedLoad(): void
    {
        // TODO
    }

    public function calculateBalance(): void
    {
        // TODO
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
}
