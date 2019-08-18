<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

use iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Locator\PsrContainerHandlerLocator;
use iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Middleware\ImscpDbTransactionMiddleware;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Plugins\LockingMiddleware;

return [
    'extractor'   => ClassNameExtractor::class,
    'locator'     => PsrContainerHandlerLocator::class,
    'inflector'   => HandleInflector::class,
    'middleware'  => [
        LockingMiddleware::class            => 999,
        ImscpDbTransactionMiddleware::class => 999,
        CommandHandlerMiddleware::class     => 0
    ],
    'handler_map' => []
];
