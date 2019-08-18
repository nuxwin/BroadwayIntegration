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

use InvalidArgumentException;
use League\Tactician\Handler\CommandHandlerMiddleware;
use Psr\Container\ContainerInterface;

/**
 * Class CommandHandlerMiddlewareFactory
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus
 */
class CommandHandlerMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return CommandHandlerMiddleware|object
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['command_bus'];

        foreach (['extractor', 'locator', 'inflector'] as $param) {
            if (!isset($config[$param]) || !is_string($config[$param])) {
                throw new InvalidArgumentException(sprintf(
                    "Missing or invalid '%s' command bus configuration parameter.",
                    $param
                ));
            }
        }

        $extractor = $container->get($config['extractor']);
        $locator = $container->get($config['locator']);
        $inflector = $container->get($config['inflector']);

        return new CommandHandlerMiddleware($extractor, $locator, $inflector);
    }
}
