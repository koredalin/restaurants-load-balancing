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
    private array $driverTransfersSetBack;

    public function __construct(
        private array $config
    ) {
        $this->globalIterations = 0;
        $this->driverTransfers = [];
        $this->impossibleTransfersToRestaurantIds = [];
        $this->driverTransfersSetBack = [];
    }

    public function getRestaurants(): array
    {
        return $this->restaurants;
    }

    public function getDriverTransfers(): array
    {
        return $this->driverTransfers;
    }

    public function getDriverTransfersSetBack(): array
    {
        return $this->driverTransfersSetBack;
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
        $nextDriverId = 1;
        foreach ($this->config['restaurants'] as $restaurantKey => $restaurantArr) {
            $restaurantDrivers = $this->getRestaurantDrivers($restaurantArr, $nextDriverId);
            $restaurant = new Restaurant($this->config, $this->config['restaurants'][$restaurantKey][0], $restaurantDrivers);
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
     *
     * @return void
     */
    public function CalculateBalance(): void
    {
        $this->calculateBalanceSingleNeedTransfers();
        foreach ($this->driverTransfers as $key => $transferGroup) {
            if (empty($transferGroup)) {
                unset($this->driverTransfers[$key]);
            }
        }
        $this->driverTransfers = array_values($this->driverTransfers);
        $this->globalIterations++;

        $isExcess = false;
        foreach ($this->getLoadByRestaurantIds() as $restaurantLoad) {
            if ($restaurantLoad < -1) {
                $isExcess = true;
                break;
            }
        }

        foreach ($this->getLoadByRestaurantIds() as $restaurantLoad) {
            if ($restaurantLoad >= 0 && $isExcess && $this->globalIterations < $this->config['restaurantsWithExcessDriversMaxGlobalIterations']) {
                $this->CalculateBalance();
            }
        }
    }

    public function getLoadByRestaurantIds(): array
    {
        $result = [];
        foreach ($this->restaurants as $restaurant) {
            $result[$restaurant->getId()] = $restaurant->load;
        }

        return $result;
    }

    /**
     * Creates a list of Drivers for a restaurant from the config file.
     *
     * @param array $restaurantArr Current restaurant from the config file.
     * @param int $driverStartId
     * @return void
     */
    private function getRestaurantDrivers(array $restaurantArr, int $driverStartId): array
    {
        $drivers = [];
        $driversCount = rand($this->config['minDriversPerRestaurant'], $this->config['maxDriversPerRestaurant']);
        for ($ii = 0; $ii < $driversCount; $ii++) {
            $driverInitialCoordinates = Location::generateRandomPoint([$restaurantArr[2], $restaurantArr[3]], $this->config['restaurantDriversRadiusInMeters']);
            $driver = new Driver($driverStartId + $ii, $driverInitialCoordinates[0], $driverInitialCoordinates[1]);
            $drivers[] = $driver;
        }

        return $drivers;
    }

    private function getRestaurantById(int $id): Restaurant
    {
        if (!array_key_exists($id, $this->restaurantsMap)) {
            throw new ApplicationException('No restaurant with id: ' . $id);
        }

        $restaurantKey = $this->restaurantsMap[$id];
        if (!array_key_exists($restaurantKey, $this->restaurants)) {
            throw new ApplicationException('No restaurant with key: ' . $restaurantKey);
        }

        return $this->restaurants[$this->restaurantsMap[$id]];
    }
    
    public function getDriverArrsByRestaurantId(): array
    {
        $result = [];
        foreach ($this->restaurants as $restaurant) {
            $result[$restaurant->getId()] = $restaurant->getDriverArrs();
        }

        return $result;
    }

    /**
     * Find the restaurant with the biggest load.
     * Find a driver to max 6000 m on a restaurant with minimal load.
     * Find the nearest driver from the second restaurant.
     * If the second restaurant has big load.
     * Continue the same practice max 2 more times. Total max of 3 driver transfers.
     * 
     * @return void
     */
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
        $restaurantTransferFrom = !empty($tranferGroupLastOperation) ? $this->getRestaurantById($tranferGroupLastOperation['transferFromRId']) : null;
        if (
            ($ii === $this->config['maxLoadCascades'] && $restaurantTransferFrom && $restaurantTransferFrom->load >= 0)
            || ($restaurantTransferTo->farAway === true && !empty($tranferGroupLastOperation))
        ) {
            $this->impossibleTransfersToRestaurantIds[] = $tranferGroup[0]['transferToRId'];
            $this->setBackLastTransfersGroup();
        }

        $this->calculateBalanceSingleNeedTransfers();
    }

    private function setBackLastTransfersGroup(): void
    {
        $lastTransferKey = array_key_last($this->driverTransfers);
        $lastTransfer = $this->driverTransfers[$lastTransferKey];
        foreach(array_reverse($lastTransfer) as $singleDriverTransfer) {
            $restaurantTransferTo = $this->getRestaurantById($singleDriverTransfer['transferToRId']);
            $restaurantTransferFrom = $this->getRestaurantById($singleDriverTransfer['transferFromRId']);
            $driver = $restaurantTransferTo->getDriverById($singleDriverTransfer['dId']);
            $restaurantTransferFrom->addDriverBack($driver);
            $restaurantTransferFrom->load--;
            $restaurantTransferTo->removeDriverById($driver->getId());
            $restaurantTransferTo->load++;
        }

        $this->driverTransfersSetBack[] = $this->driverTransfers[$lastTransferKey];
        unset($this->driverTransfers[$lastTransferKey]);
    }

    private function getHighestLoadRestaurant(): ?Restaurant
    {
        $biggestLoad = -1;
        $restaurantKey = null;
        foreach ($this->restaurants as $key => $restaurant) {
            if ($restaurant->farAway || in_array($restaurant->getId(), $this->impossibleTransfersToRestaurantIds)) {
                continue;
            }

            if ($restaurant->load > $biggestLoad) {
                $biggestLoad = $restaurant->load;
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
        $load = ceil($this->config['maxOrdersPerRestaurant'] / 2);
        $lastDriverDistance = $this->config['driverMaxTransferDistanceInMeters'];
        foreach ($this->restaurants as $neighborRestaurant) {
            // Skip the restaurant for which we are searching drivers.
            if ($neighborRestaurant->getId() === $restaurant->getId()) {
                continue;
            }

            foreach ($neighborRestaurant->getDrivers() as $driver) {
                if (
                    $neighborRestaurant->load >= $load
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
                if ($distanceBetweenRestaurantAndDriver <= $lastDriverDistance) {
                    $lastDriverDistance = $distanceBetweenRestaurantAndDriver;
                    $load = $neighborRestaurant->load;
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
        $restaurantTransferFrom->load++;
        $restaurantTransferTo->addDriver($driver);
        $restaurantTransferTo->load--;
        $this->driverTransfers[array_key_last($this->driverTransfers)][] = [
            'transferFromRId' => $restaurantTransferFrom->getId(),
            'dId' => $driver->getId(),
            'transferToRId' => $restaurantTransferTo->getId(),
        ];

        if ($restaurantTransferFrom->load < 0) {
            return null;
        } else {
            return $restaurantTransferFrom;
        }
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
