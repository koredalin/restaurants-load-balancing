<?php

namespace Drivers\Models;

use Drivers\Exceptions\ApplicationException;

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
    /**
     * Maps $this->drivers array.
     * $driverId => $this->driversKey.
     */
    private array $driversMap;
    private int $orders;
    public int $load;
    /**
     * If the restaurant is more than 6km than the nearest driver.
     */
    public bool $farAway = false;
    
    public function __construct(
        private array $config,
        int $restaurantId,
        private array $drivers
    ) {
        $this->setRestaurant($restaurantId);
        $this->setDriversMap();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }

    public function getDrivers(): array
    {
        return $this->drivers;
    }

    public function getDriversMap(): array
    {
        return $this->driversMap;
    }

    public function getDriverArrs(): array
    {
        $result = [];
        foreach ($this->drivers as $driver) {
            $result[] = $driver->toArray();
        }

        return $result;
    }

    public function getDriverById(int $driverId): Driver
    {
        $driver = $this->drivers[$this->driversMap[$driverId]] ?? null;
        if (!$driver) {
            throw new ApplicationException('No such driver.');
        }
        
        return $driver;
    }
    
    public function getOrders(): int
    {
        return $this->orders;
    }
    
    public function addDriver(Driver $driver): void
    {
        $driver->lat = $this->lat - 0.0005;
        $driver->lng = $this->lng;
        $driver->isTransferred = true;
        $this->drivers[] = $driver;
        $this->driversMap[$driver->getId()] = array_key_last($this->drivers);
    }

    public function addDriverBack(Driver $driver): void
    {
        $driver->setBack();
        $this->drivers[] = $driver;
        $this->driversMap[$driver->getId()] = array_key_last($this->drivers);
    }

    public function removeDriverById(int $driverId): void
    {
        foreach ($this->drivers as $driverKey => $driver) {
            if ($driver->getId() === $driverId) {
                unset($this->drivers[$driverKey]);
                $this->drivers = array_values($this->drivers);
                $this->setDriversMap();

                return;
            }
        }
        
        throw new ApplicationException('Restaurant id: ' . $this->id . '. No driver with id: ', $driverId);
    }

    public function createOrders(): void
    {
        $this->orders = rand($this->config['minOrdersPerRestaurant'], $this->config['maxOrdersPerRestaurant']);
    }
    
    public function calculateLoad(): void
    {
        $this->load = (int) round($this->orders / 2 - count($this->drivers));
    }

    private function setRestaurant(int $restaurantId): void
    {
        foreach ($this->config['restaurants'] as $restaurantArr) {
            if ($restaurantId === $restaurantArr[0]) {
                $this->id = $restaurantId;
                $this->name = $restaurantArr[1];
                $this->lat = $restaurantArr[2];
                $this->lng = $restaurantArr[3];

                return;
            }
        }

        throw new ApplicationException('No restaurant with id: ' . $restaurantId . ' in config file.');
    }

    private function setDriversMap(): void
    {
        $this->driversMap = [];
        foreach ($this->drivers as $key => $driver) {
            $this->driversMap[$driver->getId()] = $key;
        }
    }
}
