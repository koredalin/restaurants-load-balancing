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

    public function __construct(
        protected array $config
    ) {
        http_response_code(self::RESPONSE_CODE_SUCCESS);
        $this->serializationDir = __DIR__ . '/../../serialization';
    }
    
    protected function setHeaderContentType(string $contentType): void
    {
        header('Content-Type: ' . $contentType . '; charset=utf-8');
    }

    /**
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
}
