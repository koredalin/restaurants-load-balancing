<?php

namespace Drivers\Controllers;

use Drivers\Exceptions\ApplicationException;
use Drivers\Models\DriverBalancingSimulation;
use Drivers\Helpers\HttpManager;
use Drivers\Helpers\SerializationManager;
use Drivers\Helpers\Logger;

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
            $this->setCustomConfigDistances();
            $responseBody = [];
            $system = new DriverBalancingSimulation($this->config);
            $system->CreateRandomFreeDrivers();
            $system->RandomizedLoad();
            $responseBody['restaurantsInit'] = $system->getRestaurantByIdArrs();
            $system->CalculateBalance();
            $responseBody['driverTransfers'] = $system->getDriverTransfers();
            $responseBody['restaurantsFinal'] = $system->getRestaurantByIdArrs();
            $responseJson = json_encode($responseBody);
            SerializationManager::serialize($this->serializationDir, $responseJson);
            $this->setHeaderContentType(HttpManager::CONTENT_TYPE_JSON);

            return $responseJson;
        } catch (ApplicationException | \Exception $ex) {
            Logger::logError($this->errorsDir, $ex->getMessage());
            http_response_code(HttpManager::RESPONSE_CODE_INTERNAL_SERVER_ERROR);
            exit;
        }
    }

    public function getConfig(): string
    {
        try {
            $responseJson = json_encode($this->config);
            $this->setHeaderContentType(HttpManager::CONTENT_TYPE_JSON);

            return $responseJson;
        } catch (ApplicationException | \Exception $ex) {
            Logger::logError($this->errorsDir, $ex->getMessage());
            http_response_code(HttpManager::RESPONSE_CODE_INTERNAL_SERVER_ERROR);
            exit;
        }
    }

    /**
     * The method exchange config drivers' distances parameters if such are set as URL GET parameters.
     *
     * @return void
     */
    private function setCustomConfigDistances(): void
    {
        $restaurantDriversRadiusInMeters = (int) filter_input(INPUT_GET, 'restaurantDriversRadiusInMeters');
        if ($restaurantDriversRadiusInMeters > 0) {
            $this->config['restaurantDriversRadiusInMeters'] = $restaurantDriversRadiusInMeters;
        }

        $driverMaxTransferDistanceInMeters = (int) filter_input(INPUT_GET, 'driverMaxTransferDistanceInMeters');
        if ($driverMaxTransferDistanceInMeters > 0) {
            $this->config['driverMaxTransferDistanceInMeters'] = $driverMaxTransferDistanceInMeters;
        }
    }
}
