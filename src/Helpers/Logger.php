<?php

namespace Drivers\Helpers;

use Drivers\Helpers\DateTimeManager as DTM;
use Drivers\Exceptions\ApplicationException;

/**
 * Description of Logger
 *
 * @author H1
 */
class Logger
{
    /**
     * We need errors logging.
     *
     * @param string $errorsDir
     * @param string $error
     * @return void
     * @throws ApplicationException
     */
    public static function logError(string $errorsDir, string $error): void
    {
        $isRecorded = file_put_contents(
            $errorsDir . '/error_' . DTM::getTimeStamp() . '.txt',
            $error,
            FILE_APPEND
        );

        if ($isRecorded === false) {
            throw new ApplicationException('The error is not logged.');
        }
    }
}
