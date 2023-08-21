<?php

namespace Drivers\Controllers;

use Drivers\Exceptions\ApplicationException;
use Drivers\Models\DriverBalancingSimulation;

/**
 * Description of MainController
 *
 * @author H1
 */
class HomeController extends Controller
{
    public function generateDriverTransfers(): string
    {
        try {
            $responseBody = [];
            $responseBody['restaurants'] = $this->config['restaurants'];
            $system = new DriverBalancingSimulation($this->config);
            $system->CreateRandomFreeDrivers();
            $system->RandomizedLoad();
            $responseBody['driversByRestaurantId'] = $system->getDriverArrsByRestaurantId();
            $responseBody['restaurantsInitialLoad'] = $system->getLoadByRestaurants();
            $system->CalculateBalance();
            $responseBody['driverTransfers'] = $system->getDriverTransfers();
            $responseBody['restaurantsFinalLoad'] = $system->getLoadByRestaurants();
            $response = json_encode($responseBody);
            $this->setHeaderContentType(self::CONTENT_TYPE_JSON);

            return $response;
        } catch (ApplicationException | \Exception $ex) {
            http_response_code(self::RESPONSE_CODE_INTERNAL_SERVER_ERROR);
            exit;
        }
    }
}
