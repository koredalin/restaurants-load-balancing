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
    private int $globalIterations;
    private array $impossibleTransfersToRestaurantIds;
    /**
     * Maps $this->restaurants array.
     * $restaurantId => $this->restaurantsKey.
     */
    private array $restaurantsMap;

    public function __construct(
        private array $config
    ) {
        $this->globalIterations = 0;
    }

    public function getDriverTransfers(): array
    {
        return $this->driverTransfers;
    }

    /**
     * We create the restaurants explained in the config file.
     * For each restaurant we create a set of drivers.
     *
     * @return void
     * @throws Exception
     */
    public function CreateRandomFreeDrivers(): void
    {
        if (empty($this->config['restaurants'])) {
            throw new Exception('No restaurants set in the config.');
        }

        $this->restaurants = [];
        $this->restaurantsMap = [];
        $restaurantKey = 0;
        $nextDriverId = 0;
        foreach ($this->config['restaurants'] as $restaurantKey => $restaurantArr) {
            $restaurant = new Restaurant($this->config, $this->config['restaurants'][$restaurantKey][0], $nextDriverId);
            $this->restaurants[$restaurantKey] = $restaurant;
            $this->restaurantsMap[$restaurant->getId()] = $restaurantKey++;
            $drivers = $restaurant->getDrivers();
            $lastDriver = !empty($drivers) ? end($drivers) : null;
            $nextDriverId = $lastDriver ? $lastDriver->getId() + 1 : $nextDriverId + 1;
        }
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
        $this->impossibleTransfersToRestaurantIds = [];
        $this->calculateBalanceSingleNeedTransfers();
        foreach ($this->driverTransfers as $key => $transferGroup) {
            if (empty($transferGroup)) {
                unset($this->driverTransfers[$key]);
            }
        }
        $this->driverTransfers = array_values($this->driverTransfers);

        $isExcess = false;
        foreach ($this->getLoadByRestaurants() as $restaurantLoad) {
            if ($restaurantLoad < -1) {
                $isExcess = true;
                break;
            }
        }

        foreach ($this->getLoadByRestaurants() as $restaurantLoad) {
            if ($restaurantLoad >= 0 && $isExcess && $this->globalIterations < $this->config['restaurantsWithExcessDriversМaxGlobalIterations']) {
    echo '<h4>Rrestaurants load after estimations: '.$this->globalIterations.'</h4>'.PHP_EOL;
                $this->globalIterations++;
        print_r($this->getLoadByRestaurants());
                $this->CalculateBalance();
            }
        }
        
//        var_dump('$this->impossibleTransfersToRestaurantIds');
//        print_r($this->impossibleTransfersToRestaurantIds);
    }

    public function getLoadByRestaurants(): array
    {
        $result = [];
        foreach ($this->restaurants as $restaurant) {
            $result[$restaurant->getId()] = $restaurant->currentLoad;
        }

        return $result;
    }

    private function calculateBalanceSingleNeedTransfers(): void
    {
        $restaurantTransferTo = $this->getHighestLoadRestaurant();
        if (!$restaurantTransferTo) {
            // No restaurants that need transfers.
            return;
        }
        
        $this->driverTransfers[] = [];
        for ($ii = 0; $ii < $this->config['maxLoadCascades']; $ii++) {
            $nearestDriver = $this->getNearestLowestLoadRestaurantDriver($restaurantTransferTo);
            if (!$nearestDriver) {
                $restaurantTransferTo->farAway = true;
                break;
            } else {

                $nextRestaurantTransferTo = $this->exchangeDriver($restaurantTransferTo, $nearestDriver);
                if ($nextRestaurantTransferTo) {
                    $restaurantTransferTo = $nextRestaurantTransferTo;
                } else {
                    break;
                }
            }
        }

        $tranferGroup = $this->driverTransfers[array_key_last($this->driverTransfers)];
        $tranferGroupLastOperation = !empty($tranferGroup) ? $tranferGroup[array_key_last($tranferGroup)] : [];
        if ($ii === $this->config['maxLoadCascades'] && array_key_exists('rLoad', $tranferGroupLastOperation) && $tranferGroupLastOperation['rLoad'] >= 0) {
            $this->impossibleTransfersToRestaurantIds[] = $tranferGroup[0]['transferredToRId'];
            $this->setBackLastTransfersGroup();
            unset($this->driverTransfers[array_key_last($this->driverTransfers)]);
        }

        $this->calculateBalanceSingleNeedTransfers();
    }

    private function setBackLastTransfersGroup(): void
    {
        $lastTransferKey = array_key_last($this->driverTransfers);
        $lastTransfer = $this->driverTransfers[$lastTransferKey];
        foreach(array_reverse($lastTransfer) as $singleDriverTransfer) {
            $restaurantTransferTo = $this->restaurants[$this->restaurantsMap[$singleDriverTransfer['transferredToRId']]];
            $restaurantTransferFrom = $this->restaurants[$this->restaurantsMap[$singleDriverTransfer['rId']]];
            $driver = $restaurantTransferTo->getDriverById($singleDriverTransfer['dId']);
            $restaurantTransferFrom->addDriverBack($driver);
            $restaurantTransferFrom->currentLoad--;
            $restaurantTransferTo->removeDriverById($driver->getId());
            $restaurantTransferTo->currentLoad++;
        }
    }

    public function getRestaurants(): array
    {
        return $this->restaurants;
    }

    private function getHighestLoadRestaurant(): ?Restaurant
    {
        $biggestLoad = -1;
        $restaurantKey = null;
        foreach ($this->restaurants as $key => $restaurant) {
            if ($restaurant->farAway || in_array($restaurant->getId(), $this->impossibleTransfersToRestaurantIds)) {
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
        foreach ($this->restaurants as $neighborRestaurant) {
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
     * @param Restaurant $restaurantTransferTo
     * @param Driver $driver
     * @return Restaurant|null
     * @throws ApplicationException
     */
    private function exchangeDriver(Restaurant $restaurantTransferTo, Driver $driver): ?Restaurant
    {
        $restaurantTransferFrom = $this->getRestaurantByDriverId($driver->getId());
        $restaurantTransferFrom->removeDriverById($driver->getId());
        $restaurantTransferFrom->currentLoad++;
        $restaurantTransferTo->addDriver($driver);
        $restaurantTransferTo->currentLoad--;
        $this->driverTransfers[array_key_last($this->driverTransfers)][] = [
            'rId' => $restaurantTransferFrom->getId(),
            'rLoad' => $restaurantTransferFrom->currentLoad,
            'dId' => $driver->getId(),
            'transferredToRId' => $restaurantTransferTo->getId(),
            'transferredToRLoad' => $restaurantTransferTo->currentLoad,
        ];

        if ($restaurantTransferFrom->currentLoad < 0) {
            return null;
        } else {

            return $restaurantTransferFrom;
        }

        throw new ApplicationException('Driver not moved.');
    }

    private function getRestaurantByDriverId(int $driverId): Restaurant
    {
        foreach ($this->restaurants as $restaurant) {
            if (array_key_exists($driverId, $restaurant->getDriversMap())) {
                return $restaurant;
            }
        }

        throw new ApplicationException('No restaurant with driver id: ' . $driverId);
    }
}
