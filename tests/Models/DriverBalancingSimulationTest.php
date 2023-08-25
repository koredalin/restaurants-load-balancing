<?php

declare(strict_types=1);

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Tests\TestHelper;
use Drivers\Models\Restaurant;
use Drivers\Models\DriverBalancingSimulation as Simulator;

/**
 * Description of DriverBalancingSimulationTest
 *
 * @author H1
 */
class DriverBalancingSimulationTest extends TestCase
{
    private array $config;
    private array $restaurants;
    private Simulator $simulator;
    private array $driversByRestaurantIdInit;
    private array $initLoad;
    private array $driverTransfers;
    private array $driversByRestaurantIdFinal;
    private array $finalLoad;
    
    protected function setUp(): void
    {
        $this->config = require TestHelper::CONFIG_FILE_PATH;
        $this->simulator = new Simulator($this->config);
        $this->simulator->CreateRandomFreeDrivers();
        $this->driversByRestaurantIdInit = $this->simulator->getDriverArrsByRestaurantId();
        $this->simulator->RandomizedLoad();
        $this->restaurants = $this->simulator->getRestaurants();
        $this->initLoad = $this->simulator->getLoadByRestaurantIds();
    }

    public function testInitData(): void
    {
        // There are 11 restaurants set into the config file.
        $this->assertSame(11, count($this->restaurants));
        foreach ($this->restaurants as $key => $restaurant) {
            $this->assertInstanceOf(Restaurant::class, $restaurant);
            $this->assertSame($this->config['restaurants'][$key][0], $restaurant->getId());
        }

        $this->assertSame(array_column($this->config['restaurants'], 0), array_keys($this->initLoad));
        foreach ($this->initLoad as $load) {
            $this->assertIsInt($load);
        }
    }

    public function testDriverTransfers(): void
    {
        $this->simulator->CalculateBalance();
        $this->driversByRestaurantIdFinal = $this->simulator->getDriverArrsByRestaurantId();
        $this->driverTransfers = $this->simulator->getDriverTransfers();
        $this->finalLoad = $this->simulator->getLoadByRestaurantIds();

        if (empty(array_diff($this->initLoad, $this->finalLoad))) {
            var_dump('Empty Driver Transfers.');
            $this->assertEmpty($this->driverTransfers);
            $this->assertTrue(json_encode($this->driversByRestaurantIdInit) === json_encode($this->driversByRestaurantIdFinal));
        } else {
            var_dump('Driver Transfers.');
            $this->assertNotEmpty($this->driverTransfers);
            $this->assertFalse(empty(array_diff($this->driversByRestaurantIdInit, $this->driversByRestaurantIdFinal)));
            $this->assertTrue(count($this->initLoad) === count($this->finalLoad));
            $this->assertSame(array_keys($this->initLoad), array_keys($this->finalLoad));
        }
    }
}
