<?php

namespace Drivers\Models;

use Drivers\Exceptions\ApplicationException;
use Drivers\Helpers\Location;

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
    /**
     * Maps $this->drivers array.
     * $driverId => $this->driversKey.
     */
    private array $driversMap;
    private int $orders;
    public int $currentLoad;
    /**
     * If the restaurant is more than 6km than the nearest driver.
     */
    public bool $farAway = false;
    
    public function __construct(
        private array $config,
        int $restaurantId,
        int $driverStartId
    ) {
        $this->setRestaurant($restaurantId);
        $this->createDrivers($driverStartId);
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
        $driver->restaurantId = $this->id;
        $driver->lat = $this->lat;
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
                unset($this->driversMap[$driver->getId()]);
                unset($this->drivers[$driverKey]);
                array_values($this->drivers);

                return;
            }
        }
    }

    public function createOrders(): void
    {
        $this->orders = rand($this->config['minOrdersPerRestaurant'], $this->config['maxOrdersPerRestaurant']);
    }
    
    public function calculateLoad(): void
    {
        $this->currentLoad = (int) round($this->orders / 2 - count($this->drivers));
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
            throw new ApplicationException('No such restaurant id in config list.');
        }
    }
    
    private function createDrivers(int $driverStartId): void
    {
        $this->drivers = [];
        $this->driversMap = [];
        $driverKey = 0;
        $driversCount = rand($this->config['minDriversPerRestaurant'], $this->config['maxDriversPerRestaurant']);
        for ($ii = 0; $ii < $driversCount; $ii++) {
            $driverInitialCoordinates = Location::generateRandomPoint([$this->lat, $this->lng], $this->config['driverMaxTransferDistanceInMeters']);
            $driver = new Driver($this->config, $driverStartId + $ii, $this->id, $driverInitialCoordinates[0], $driverInitialCoordinates[1]);
            $this->drivers[$driverKey] = $driver;
            $this->driversMap[$driver->getId()] = $driverKey++;
        }
    }
}
