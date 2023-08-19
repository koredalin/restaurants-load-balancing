<?php

namespace Drivers\Models;

/**
 * Description of Driver
 *
 * @author H1
 */
class Driver
{
    public float $lat;
    public float $lng;
    public bool $isTransferred;
    
    public function __construct(
        private int $id
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function calculateCoordinates(float $restaurantLat, float $restaurantLng): void
    {
        // TODO
    }

    public function toArray(): array
    {
        return [
            $this->id,
            $this->lat,
            $this->lng,
            $this->isTransferred,
        ];
    }
}
