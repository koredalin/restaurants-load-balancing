<?php

namespace Drivers\Helpers;

use Drivers\Helpers\DateTimeManager as DTM;
use Drivers\Exceptions\ApplicationException;

/**
 * Description of SerializationManager
 *
 * @author H1
 */
class SerializationManager
{
    /**
     * It will be nice if we have a serialization for the data, so we will not need to estimate it multiple times.
     *
     * @param string $serializationDir
     * @param string $text
     * @return void
     */
    public static function serialize(string $serializationDir, string $text): void
    {
        $isRecorded = null;
        if ((string) filter_input(INPUT_GET, 'serialize') === '1') {
            $isRecorded = file_put_contents(
                $serializationDir . '/driver_transfers_' . DTM::getTimeStamp() . '.json',
                $text
            );
        }

        if ($isRecorded === false) {
            throw new ApplicationException('There is no serialization.');
        }
    }
}
