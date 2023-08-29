<?php

namespace Drivers\Helpers;

/**
 * Description of Location
 *
 * @author H1
 */
class LocationManager
{
    private const EARTH_RADIUS_IN_METERS = 6371000;

    /**
     * Source: https://stackoverflow.com/a/10054282/3144561
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     *
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @return float Distance between points in [m] (same as earthRadius)
    */
    public static function calculateDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo): float
    {
      // convert from degrees to radians
      $latFrom = deg2rad($latitudeFrom);
      $lonFrom = deg2rad($longitudeFrom);
      $latTo = deg2rad($latitudeTo);
      $lonTo = deg2rad($longitudeTo);

      $lonDelta = $lonTo - $lonFrom;
      $a = pow(cos($latTo) * sin($lonDelta), 2) +
        pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
      $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

      $angle = atan2(sqrt($a), $b);

      return $angle * self::EARTH_RADIUS_IN_METERS;
    }

    /**
     * Source: https://stackoverflow.com/a/42360442/3144561
     *
     * @param array $centre
     * @param int $radiusInMeters
     * @return array
     */
    public static function generateRandomPoint(array $centre, int $radiusInMeters): array
    {
        //Pick random distance within $distance;
        $distance = lcg_value() * $radiusInMeters;

        //Convert degrees to radians.
        $centre_rads = array_map( 'deg2rad', $centre );

        //First suppose our point is the north pole.
        //Find a random point $distance meters away
        $latRads = (pi()/2) - $distance / self::EARTH_RADIUS_IN_METERS;
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
