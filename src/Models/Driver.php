<?php

namespace Drivers\Models;

use Drivers\Helpers\Location;

/**
 * Description of Driver
 *
 * @author H1
 */
class Driver
{
    /**
     * Restaurant ids that the driver could be transferred to.
     */
    private array $possibleTransfers;
    public bool $isTransferred = false;
    
    public function __construct(
        private array $config,
        private int $id,
        private int $initialRestaurantId,
        private float $initialLat,
        private float $initialLng
    ) {
        $this->calculatePossibleTransfers();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPossibleTransfers(): array
    {
        return $this->possibleTransfers;
    }

    public function toArray(): array
    {
        return [
            $this->id,
            $this->initialRestaurantId,
            $this->initialLat,
            $this->initialLng,
            $this->possibleTransfers,
            $this->isTransferred,
        ];
    }
    
    public function calculatePossibleTransfers(): void
    {
        $this->possibleTransfers = [];
        foreach ($this->config['restaurants'] as $restaurantArr) {
            if ($this->initialRestaurantId === $restaurantArr[0]) {
                continue;
            }

            $distance = Location::calculateDistance($this->initialLat, $this->initialLng, $restaurantArr[2], $restaurantArr[3]);
            if ($distance <= $this->config['driverMaxTransferDistanceInMeters']) {
                $this->possibleTransfers[] = $restaurantArr[0];
            }
        }
    }
}
