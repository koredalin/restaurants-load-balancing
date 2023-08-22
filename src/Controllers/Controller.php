<?php

namespace Drivers\Controllers;

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

    public function __construct(
        protected array $config
    ) {
        http_response_code(self::RESPONSE_CODE_SUCCESS);
    }
    
    protected function setHeaderContentType(string $contentType): void
    {
        header('Content-Type: ' . $contentType . '; charset=utf-8');
    }
}
