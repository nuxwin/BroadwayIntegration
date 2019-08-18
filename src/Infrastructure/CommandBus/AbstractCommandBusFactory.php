<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

declare(strict_types=1);

namespace iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * Class AbstractCommandBusFactory
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus
 */
class AbstractCommandBusFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return AbstractCommandBus
     */
    public function __invoke(
        ContainerInterface $container, string $requestedName
    ): AbstractCommandBus
    {
        $middleware = $this->getCommandBusMiddlewareConfig($container);
        arsort($middleware);

        $middlewareInstances = [];
        foreach (array_keys($middleware) as $serviceName) {
            $middlewareInstances[] = $container->get($serviceName);
        }

        return new $requestedName($middlewareInstances);
    }

    /**
     * Returns configuration for command bus middleware.
     *
     * @param ContainerInterface $container
     * @return array
     */
    private function getCommandBusMiddlewareConfig(
        ContainerInterface $container
    ): array
    {
        $config = $container->get('config');

        if (!isset($config['command_bus'])
            || !is_array($config['command_bus'])
        ) {
            throw new InvalidArgumentException(
                "Missing or invalid configuration for command bus."
            );
        }

        $middleware = $config['command_bus']['middleware'] ?? NULL;

        if (!isset($middleware) || !is_array($middleware)) {
            throw new InvalidArgumentException(sprintf(
                "Missing or invalid 'middleware' configuration for command bus."
            ));
        }

        return $middleware;
    }
}
