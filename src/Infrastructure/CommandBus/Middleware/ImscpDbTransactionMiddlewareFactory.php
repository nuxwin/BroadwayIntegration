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

namespace iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Middleware;

use iMSCP_Database;
use iMSCP_Exception_Database;
use Psr\Container\ContainerInterface;

/**
 * Class ImscpDbTransactionMiddlewareFactory
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Middleware
 */
class ImscpDbTransactionMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return ImscpDbTransactionMiddleware
     * @throws iMSCP_Exception_Database
     */
    public function __invoke(
        ContainerInterface $container
    ): ImscpDbTransactionMiddleware
    {
        $db = iMSCP_Database::getInstance();

        return new ImscpDbTransactionMiddleware($db);
    }
}
