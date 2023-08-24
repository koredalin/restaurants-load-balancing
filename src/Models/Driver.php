<?php

namespace Drivers\Models;

/**
 * Description of Driver
 *
 * @author H1
 */
class Driver
{
    public bool $isTransferred = false;
    private float $initialLat;
    private float $initialLng;

    public function __construct(
        private int $id,
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
