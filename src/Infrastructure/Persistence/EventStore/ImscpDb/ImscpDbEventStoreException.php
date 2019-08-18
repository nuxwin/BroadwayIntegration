<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

/**
 * @noinspection PhpUnhandledExceptionInspection PhpDocMissingThrowsInspection
 */

declare(strict_types=1);

namespace iMSCP\Plugin\BroadWayIntegration\Infrastructure\Persistence\EventStore\ImscpDb;

use Broadway\EventStore\EventStoreException;
use Throwable;

/**
 * Class ImscpDbEventStoreException
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\Persistence\EventStore\ImscpDb
 */
class ImscpDbEventStoreException extends EventStoreException
{
    /**
     * @param Throwable $exception
     * @return ImscpDbEventStoreException
     */
    public static function create(Throwable $exception)
    {
        return new ImscpDbEventStoreException(
            $exception->getMessage(), 0, $exception
        );
    }
}
