<?php

namespace Drivers\Controllers;

use Drivers\Exceptions\ApplicationException;
use Drivers\Models\DriverBalancingSimulation;
use Drivers\Helpers\HttpManager;

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
            $responseBody['driversByRestaurantIdInit'] = $system->getDriverArrsByRestaurantId();
            $responseBody['restaurantsInitialLoad'] = $system->getLoadByRestaurantIds();
            $system->CalculateBalance();
            $responseBody['driverTransfers'] = $system->getDriverTransfers();
            $responseBody['driversByRestaurantIdFinal'] = $system->getDriverArrsByRestaurantId();
            $responseBody['restaurantsFinalLoad'] = $system->getLoadByRestaurantIds();
            $responseJson = json_encode($responseBody);
            $this->serialize($responseJson);
            $this->setHeaderContentType(HttpManager::CONTENT_TYPE_JSON);

            return $responseJson;
        } catch (ApplicationException | \Exception $ex) {
            $this->logError($ex->getMessage());
            http_response_code(HttpManager::RESPONSE_CODE_INTERNAL_SERVER_ERROR);
            exit;
        }
    }
}
