<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

use Broadway\EventHandling\EventBus;
use Broadway\EventStore\ConcurrencyConflictResolver\ConcurrencyConflictResolver;
use Broadway\EventStore\ConcurrencyConflictResolver\WhitelistConcurrencyConflictResolver;
use Broadway\EventStore\EventStore;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Locator\PsrContainerHandlerLocator;
use iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Locator\PsrContainerHandlerLocatorFactory;
use iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Middleware\CommandHandlerMiddlewareFactory;
use iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Middleware\ImscpDbTransactionMiddleware;
use iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Middleware\ImscpDbTransactionMiddlewareFactory;
use iMSCP\Plugin\BroadWayIntegration\Infrastructure\EventBus\EventBusFactory;
use iMSCP\Plugin\BroadWayIntegration\Infrastructure\Persistence\EventStore\ImscpDb\ImscpDbEventStoreFactory;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Handler\MethodNameInflector\InvokeInflector;
use League\Tactician\Plugins\LockingMiddleware;

return [
    'invokables' => [
        // Command bus
        ClassNameExtractor::class          => ClassNameExtractor::class,
        HandleInflector::class             => HandleInflector::class,
        LockingMiddleware::class           => LockingMiddleware::class,
        InvokeInflector::class             => InvokeInflector::class,
        // Event store
        ConcurrencyConflictResolver::class => WhitelistConcurrencyConflictResolver::class,
        // UUID
        UuidGeneratorInterface::class      => Version4Generator::class
    ],
    'factories'  => [
        // Persistence (event store)
        EventStore::class                   => ImscpDbEventStoreFactory::class,
        // Command bus
        CommandHandlerMiddleware::class     => CommandHandlerMiddlewareFactory::class,
        ImscpDbTransactionMiddleware::class => ImscpDbTransactionMiddlewareFactory::class,
        PsrContainerHandlerLocator::class   => PsrContainerHandlerLocatorFactory::class,
        // Event bus
        EventBus::class                     => EventBusFactory::class,
    ]
];
