<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

declare(strict_types=1);

namespace iMSCP\Plugin\BroadWayIntegration\Infrastructure\EventBus;

use Broadway\EventHandling\EventListener;
use Broadway\EventHandling\SimpleEventBus;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * Class EventBusFactory
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\EventBus
 */
class EventBusFactory
{
    /**
     * @param ContainerInterface $container
     * @return SimpleEventBus
     */
    public function __invoke(ContainerInterface $container): SimpleEventBus
    {
        $eventBus = new SimpleEventBus();
        $subscribers = $container->get('config')['event_bus']['subscribers'];

        if (!is_array($subscribers)) {
            throw new InvalidArgumentException(
                'Missing or invalid configuration for event bus subscribers.'
            );
        }

        foreach ($subscribers as $name) {
            $eventListener = $container->get($name);

            if (!$eventListener instanceof EventListener) {
                throw new InvalidArgumentException(sprintf(
                    'An event bus subscriber must be an instance of %s, %s given.',
                    EventListener::class,
                    is_object($eventListener)
                        ? get_class($eventListener) : gettype($eventListener)
                ));
            }

            $eventBus->subscribe($eventListener);
        }

        return $eventBus;
    }
}
