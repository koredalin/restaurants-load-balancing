<?php

namespace Drivers\Models;

/**
 * Description of Restaurant
 *
 * @author H1
 */
class Restaurant
{
    private int $id = 0;
    private string $name;
    private float $lat;
    private float $lng;
    private array $drivers;
    private int $orders;
    public int $currentLoad;
    
    public function __construct(
        private array $config,
        int $restaurantId,
        int $driverStartId
    ) {
        $this->setRestaurant($restaurantId);
        $this->createDrivers($driverStartId);
        $this->createOrders();
    }
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function getDrivers(): array
    {
        return $this->drivers;
    }
    
    public function getOrders(): int
    {
        return $this->orders;
    }
    
    public function addDriver(Driver $driver): void
    {
        $this->drivers[] = $driver;
    }
    
    public function removeDriver(int $idDriver): void
    {
        foreach ($this->drivers as $driverKey => $driver) {
            if ($driver.id === $idDriver) {
                unset($this->drivers[$driverKey]);
                array_values($this->drivers);

                return;
            }
        }
    }
    
    public function calculateLoad(): void
    {
        // TODO
    }

    private function setRestaurant(int $restaurantId): void
    {
        foreach ($this->config['restaurants'] as $restaurantArr) {
            if ($restaurantId === $restaurantArr[0]) {
                $this->id = $restaurantId;
                $this->name = $restaurantArr[1];
                $this->lat = $restaurantArr[2];
                $this->lng = $restaurantArr[3];
            }
        }
        
        if ($this->id < 1) {
            throw new \Exception('No such restaurant id in config list.');
        }
    }
    
    private function createDrivers(int $driverStartId): void
    {
        $this->drivers = [];
        $driversCount = rand($this->config['minDriversPerRestaurant'], $this->config['maxDriversPerRestaurant']);
        for ($ii = 0; $ii < $driversCount; $ii++) {
            $driver = new Driver($driverStartId + $ii, $this->lat, $this->lng, $this->config['driverMaxTransferDistanceInMeters']);
            $this->drivers[] = $driver;
        }
    }

    private function createOrders(): void
    {
        $this->orders = rand($this->config['minOrdersPerRestaurant'], $this->config['maxOrdersPerRestaurant']);
    }
}
