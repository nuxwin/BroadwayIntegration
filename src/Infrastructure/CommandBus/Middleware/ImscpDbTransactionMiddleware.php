<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

declare(strict_types=1);

namespace iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Middleware;

use iMSCP_Database;
use League\Tactician\Middleware;
use Throwable;

/**
 * Class ImscpDbTransactionMiddleware
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Middleware
 */
class ImscpDbTransactionMiddleware implements Middleware
{
    /**
     * @var iMSCP_Database
     */
    private $db;

    /**
     * ImscpDbTransactionMiddleware constructor.
     *
     * @param iMSCP_Database $db
     */
    public function __construct(iMSCP_Database $db)
    {
        $this->db = $db;
    }

    /**
     * Wraps command execution inside an iMSCP_Database transaction.
     *
     * @param object $command
     * @param callable $next
     * @return mixed
     * @throws Throwable
     */
    public function execute($command, callable $next)
    {
        $this->db->beginTransaction();

        try {
            $ret = $next($command);
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();

            throw $exception;
        }

        return $ret;
    }
}
