<?php

declare(strict_types=1);

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Tests\TestHelper;
use Drivers\Models\Driver;
use Drivers\Models\Restaurant;
use Drivers\Exceptions\ApplicationException;

/**
 * Description of RestaurantTest
 *
 * @author H1
 */
class RestaurantTest extends TestCase
{
    private array $config;
    private const ID = 13;
    private const LAT = 42.6982608;
    private const LNG = 23.3078595;
    private Restaurant $restaurant;
    private array $drivers;
    private array $driversInitData;

    protected function setUp(): void
    {
        $this->config = require TestHelper::CONFIG_FILE_PATH;

        $this->driversInitData = [
            [4, 22.123, 32.345, false],
            [5, 24.123, 34.345, false],
            [6, 27.123, 37.345, false],
        ];
        $this->drivers = [];
        $this->drivers[] = new Driver($this->driversInitData[0][0], $this->driversInitData[0][1], $this->driversInitData[0][2]);
        $this->drivers[] = new Driver($this->driversInitData[1][0], $this->driversInitData[1][1], $this->driversInitData[1][2]);
        $this->drivers[] = new Driver($this->driversInitData[2][0], $this->driversInitData[2][1], $this->driversInitData[2][2]);

        $this->restaurant = new Restaurant(
            $this->config,
            self::ID,
            $this->drivers
        );
    }

    public function testInitData(): void
    {
        $this->assertSame(self::ID, $this->restaurant->getId());
        $this->assertSame(self::LAT, $this->restaurant->getLat());
        $this->assertSame(self::LNG, $this->restaurant->getLng());
        $this->assertSame($this->drivers, $this->restaurant->getDrivers());
        $this->assertSame($this->driversInitData, $this->restaurant->getDriverArrs());
        $this->assertSame($this->drivers[1], $this->restaurant->getDriverById(5));
    }

    public function testDriverOperations(): void
    {
        $this->restaurant->removeDriverById(4);
        $expectedDrivers = $this->drivers;
        unset($expectedDrivers[0]);
        $expectedDrivers = array_values($expectedDrivers);
        $this->assertSame($expectedDrivers, $this->restaurant->getDrivers());

        $this->restaurant->addDriverBack($this->drivers[0]);
        $expectedDrivers[] = $this->drivers[0];
        $this->assertSame($expectedDrivers, $this->restaurant->getDrivers());
    }

    public function testCreateNonExistingRestaurant(): void
    {
        $this->expectException(ApplicationException::class);
        $this->expectExceptionMessage('No restaurant with id: 3 in config file.');
        $restaurant = new Restaurant(
            $this->config,
            3,
            $this->drivers
        );
    }
}
