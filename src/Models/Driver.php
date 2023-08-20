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
    private int $initialRestaurantId;
    private float $initialLat;
    private float $initialLng;

    public function __construct(
        private array $config,
        private int $id,
        public int $restaurantId,
        public float $lat,
        public float $lng
    ) {
        $this->initialRestaurantId = $this->restaurantId;
        $this->initialLat = $this->lat;
        $this->initialLng = $this->lng;
        $this->calculatePossibleTransfers();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setBack(): void
    {
        $this->restaurantId = $this->initialRestaurantId;
        $this->lat = $this->initialLat;
        $this->lng = $this->initialLng;
        $this->isTransferred = false;
    }

    public function getPossibleTransfers(): array
    {
        return $this->possibleTransfers;
    }

    public function toArray(): array
    {
        return [
            $this->id,
            $this->restaurantId,
            $this->lat,
            $this->lng,
            $this->possibleTransfers,
            $this->isTransferred,
        ];
    }
    
    public function calculatePossibleTransfers(): void
    {
        $this->possibleTransfers = [];
        foreach ($this->config['restaurants'] as $restaurantArr) {
            if ($this->restaurantId === $restaurantArr[0]) {
                continue;
            }

            $distance = Location::calculateDistance($this->lat, $this->lng, $restaurantArr[2], $restaurantArr[3]);
            if ($distance <= $this->config['driverMaxTransferDistanceInMeters']) {
                $this->possibleTransfers[] = $restaurantArr[0];
            }
        }
    }
}
