<?php

declare(strict_types=1);

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Tests\TestHelper;
use Drivers\Models\Driver;

/**
 * Description of DriverTest
 *
 * @author H1
 */
class DriverTest extends TestCase
{
    private array $config;
    private const ID = 4;
    private const INIT_LAT = 3.456;
    private const INIT_LNG = 5.567;
    private Driver $driver;
    
    protected function setUp(): void
    {
        $this->config = require TestHelper::CONFIG_FILE_PATH;
        $this->driver = new Driver(self::ID, self::INIT_LAT, self::INIT_LNG);
    }

    public function testInitData(): void
    {
        $this->assertSame(self::ID, $this->driver->getId());
        $this->assertSame(self::INIT_LAT, $this->driver->lat);
        $this->assertSame(self::INIT_LNG, $this->driver->lng);
        $this->assertSame(false, $this->driver->isTransferred);

        $driverArr = $this->driver->toArray();

        $expectedResult = [
            self::ID,
            self::INIT_LAT,
            self::INIT_LNG,
            false,
        ];
        $this->assertSame($expectedResult, $driverArr);
    }

    public function testSetBack(): void
    {
        $lat = 21.3409;
        $lng = 27.3409;
        $isTransferred = true;
        $this->driver->lat = $lat;
        $this->driver->lng = $lng;
        $this->driver->isTransferred = $isTransferred;

        $expectedResult = [
            self::ID,
            $lat,
            $lng,
            $isTransferred,
        ];
        $this->assertSame($expectedResult, $this->driver->toArray());

        $this->driver->setBack();
        
        $this->testInitData();
    }
}
