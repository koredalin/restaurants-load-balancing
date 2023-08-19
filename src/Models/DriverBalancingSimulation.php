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
    
    private function createRestaurants(): void
    {
        if (empty($this->config['restaurants'])) {
            throw new Exception('No restaurants set in the config.');
        }

        $this->restaurants = [];
        $restaurant = new Restaurant($this->config, $this->config['restaurants'][0][0], 1);
        $this->restaurants[] = $restaurant;
        $drivers = $restaurant->getDrivers();
        $lastDriver = !empty($drivers) ? end($drivers) : null;
        $nextDriverId = $lastDriver ? $lastDriver->getId() + 1 : 1;
        foreach ($this->config['restaurants'] as $restaurantKey => $restaurantArr) {
            if ($restaurantKey === 0) {
                continue;
            }

            $restaurant = new Restaurant($this->config, $this->config['restaurants'][$restaurantKey][0], $nextDriverId);
            $this->restaurants[] = $restaurant;
            $drivers = $restaurant->getDrivers();
            $lastDriver = !empty($drivers) ? end($drivers) : null;
            $nextDriverId = $lastDriver ? $lastDriver->getId() + 1 : $nextDriverId + 1;
        }
    }
}
