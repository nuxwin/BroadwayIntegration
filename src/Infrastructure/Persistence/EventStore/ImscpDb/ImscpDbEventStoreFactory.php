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

use Broadway\Serializer\SimpleInterfaceSerializer;
use iMSCP_Database;
use Psr\Container\ContainerInterface;

/**
 * Class ImscpDbEventStoreFactory
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\Persistence\EventStore\ImscpDb
 */
class ImscpDbEventStoreFactory
{
    /**
     * @param ContainerInterface $container
     * @return ImscpDbEventStore
     */
    public function __invoke(ContainerInterface $container): ImscpDbEventStore
    {
        $serializer = new SimpleInterfaceSerializer();
        $db = iMSCP_Database::getInstance();
        $table = $container->get('config')['persistence']['event_store_table'];

        return new ImscpDbEventStore($db, $serializer, $serializer, $table);
    }
}
