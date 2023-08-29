<?php

namespace Drivers\Controllers;

use Drivers\Helpers\HttpManager;

/**
 * Description of Controller
 *
 * @author H1
 */
class Controller
{
    protected string $serializationDir;
    protected string $errorsDir;

    public function __construct(
        protected array $config
    ) {
        http_response_code(HttpManager::RESPONSE_CODE_SUCCESS);
        $this->serializationDir = __DIR__ . '/../../serialization';
        $this->errorsDir = __DIR__ . '/../../error_logs';
    }
    
    protected function setHeaderContentType(string $contentType): void
    {
        header('Content-Type: ' . $contentType . '; charset=utf-8');
    }
}
