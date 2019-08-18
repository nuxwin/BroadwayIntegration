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

use Broadway\EventStore\ConcurrencyConflictResolver\BlacklistConcurrencyConflictResolver;
use Broadway\EventStore\ConcurrencyConflictResolver\ConcurrencyConflictResolver;
use Broadway\EventStore\ConcurrencyConflictResolver\WhitelistConcurrencyConflictResolver;
use Broadway\EventStore\EventStore;
use Psr\Container\ContainerInterface;

/**
 * Class ImscpDbConcurrencyEventStoreDelegatorFactory
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\Persistence\EventStore\ImscpDb
 */
class ImscpDbConcurrencyEventStoreDelegatorFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @return EventStore
     */
    public function __invoke(
        ContainerInterface $container, $name, callable $callback
    ): EventStore
    {
        $eventStore = call_user_func($callback);
        $config = $container->get('config')['persistence']['concurrency_conflict_resolver'];

        if (!is_array($config)) {
            return $eventStore;
        }

        $resolver = $container->get(ConcurrencyConflictResolver::class);
        $events = $config[get_class($resolver)] ?: [];

        if (!$events) {
            return $eventStore;
        }

        // Duck-Typing (blacklist or whitelist concurrency conflict resolver?)
        if (method_exists($resolver, 'registerConflictingEvents')) {
            $this->registerConflictingEvents($resolver, $events);
        } elseif (method_exists($resolver, 'registerIndependentEvents')) {
            $this->registerIndependentEvents($resolver, $events);
        } else {
            return $eventStore;
        }

        return new ImscpConcurrencyEventStore($eventStore, $resolver);
    }

    /**
     * Registers conflicting events onto the blacklist concurrency conflict
     * resolver.
     *
     * @param BlacklistConcurrencyConflictResolver $resolver
     * @param string[] $conflictingEvents
     */
    private function registerConflictingEvents(
        BlacklistConcurrencyConflictResolver $resolver,
        array $conflictingEvents
    ): void
    {
        foreach ($conflictingEvents as $eventClass1 => $eventClasses) {
            foreach ((array)$eventClasses as $eventClass2) {
                $resolver->registerConflictingEvents(
                    $eventClass1, $eventClass2
                );
            }
        }
    }

    /**
     * Registers independent events onto the whitelist concurrency conflict
     * resolver.
     *
     * @param WhitelistConcurrencyConflictResolver $resolver
     * @param array $independentEvents
     */
    private function registerIndependentEvents(
        WhitelistConcurrencyConflictResolver $resolver,
        array $independentEvents
    ): void
    {
        foreach ($independentEvents as $eventClass1 => $eventClasses) {
            foreach ((array)$eventClasses as $eventClass2) {
                $resolver->registerIndependentEvents(
                    $eventClass1, $eventClass2
                );
            }
        }
    }
}
