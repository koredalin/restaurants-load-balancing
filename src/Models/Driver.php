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
    private float $initialLat;
    private float $initialLng;

    public function __construct(
        private int $id,
        public int $restaurantId,
        public float $lat,
        public float $lng
    ) {
        $this->initialLat = $this->lat;
        $this->initialLng = $this->lng;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setBack(): void
    {
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
            $this->lat,
            $this->lng,
            $this->possibleTransfers,
            $this->isTransferred,
        ];
    }
}
