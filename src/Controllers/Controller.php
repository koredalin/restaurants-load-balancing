<?php

namespace Drivers\Controllers;

use Drivers\Helpers\DateTimeManager as DTM;
use Drivers\Exceptions\ApplicationException;

/**
 * Description of Controller
 *
 * @author H1
 */
class Controller
{
    protected const RESPONSE_CODE_SUCCESS = 200;
    protected const RESPONSE_CODE_INTERNAL_SERVER_ERROR = 500;
    protected const CONTENT_TYPE_HTML = 'text/html';
    protected const CONTENT_TYPE_JS = 'application/javascript';
    protected const CONTENT_TYPE_JSON = 'application/json';
    protected string $serializationDir;
    protected string $errorsDir;

    public function __construct(
        protected array $config
    ) {
        http_response_code(self::RESPONSE_CODE_SUCCESS);
        $this->serializationDir = __DIR__ . '/../../serialization';
        $this->errorsDir = __DIR__ . '/../../errors';
    }
    
    protected function setHeaderContentType(string $contentType): void
    {
        header('Content-Type: ' . $contentType . '; charset=utf-8');
    }

    /**
     * It will be nice if we have a serialization for the data, so we will not need to estimate it multiple times.
     *
     * @param string $text
     * @return void
     */
    protected function serialize(string $text): void
    {
        $isRecorded = null;
        if ((string) filter_input(INPUT_GET, 'serialize') === '1') {
            $isRecorded = file_put_contents($this->serializationDir . '/driver_transfers_' . DTM::getTimeStamp() . '.json', $text);
        }

        if ($isRecorded === false) {
            throw new ApplicationException('There is no serialization.');
        }
    }

    /**
     * We need errors logging.
     *
     * @param string $error
     * @return void
     * @throws ApplicationException
     */
    protected function logError(string $error): void
    {
        $isRecorded = file_put_contents($this->errorsDir . '/error_' . DTM::getTimeStamp() . '.txt', $error);

        if ($isRecorded === false) {
            throw new ApplicationException('The error is not logged.');
        }
    }
}
