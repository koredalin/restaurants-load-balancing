<?php

namespace Drivers\Models;

/**
 * Description of Driver
 *
 * @author H1
 */
class Driver
{
    public float $initialLat;
    public float $initialLng;
    public bool $isTransferred;
    
    public function __construct(
        private int $id,
        float $restaurantLat,
        float $restaurantLng,
        int $maxDistanceInMeters
    ) {
        $initialCoordinate = $this->generateRandomPoint([$restaurantLat, $restaurantLng], $maxDistanceInMeters);
        $this->initialLat = $initialCoordinate[0];
        $this->initialLng = $initialCoordinate[1];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            $this->id,
            $this->initialLat,
            $this->initialLng,
            $this->isTransferred,
        ];
    }

    // Source: https://stackoverflow.com/a/42360442/3144561
    private function generateRandomPoint(array $centre, int $radiusInMeters): array
    {
        $radiusEarthInMeters = 6371000; //meters

        //Pick random distance within $distance;
        $distance = lcg_value() * $radiusInMeters;

        //Convert degrees to radians.
        $centre_rads = array_map( 'deg2rad', $centre );

        //First suppose our point is the north pole.
        //Find a random point $distance meters away
        $latRads = (pi()/2) - $distance/$radiusEarthInMeters;
        $lngRads = lcg_value()*2*pi();


        //($lat_rads,$lng_rads) is a point on the circle which is
        //$distance miles from the north pole. Convert to Cartesian
        $x1 = cos( $latRads ) * sin( $lngRads );
        $y1 = cos( $latRads ) * cos( $lngRads );
        $z1 = sin( $latRads );


        //Rotate that sphere so that the north pole is now at $centre.

        //Rotate in x axis by $rot = (pi()/2) - $centre_rads[0];
        $rot = (pi()/2) - $centre_rads[0];
        $x2 = $x1;
        $y2 = $y1 * cos( $rot ) + $z1 * sin( $rot );
        $z2 = -$y1 * sin( $rot ) + $z1 * cos( $rot );

        //Rotate in z axis by $rot = $centre_rads[1]
        $rot = $centre_rads[1];
        $x3 = $x2 * cos( $rot ) + $y2 * sin( $rot );
        $y3 = -$x2 * sin( $rot ) + $y2 * cos( $rot );
        $z3 = $z2;


        //Finally convert this point to polar co-ords
        $lngRads = atan2( $x3, $y3 );
        $latRads = asin( $z3 );

        return array_map( 'rad2deg', array( $latRads, $lngRads ) );
    }
}
