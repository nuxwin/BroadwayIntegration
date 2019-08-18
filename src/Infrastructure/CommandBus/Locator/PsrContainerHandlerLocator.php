<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

declare(strict_types=1);

namespace iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Locator;

use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\Locator\HandlerLocator;
use Psr\Container\ContainerInterface;

/**
 * Class PsrContainerHandlerLocator
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Locator
 */
class PsrContainerHandlerLocator implements HandlerLocator
{
    /**
     * @var array
     */
    protected $handlerMap;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * PsrContainerHandlerLocator constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function getHandlerForCommand($commandName)
    {
        if (!$this->commandExists($commandName)) {
            throw MissingHandlerException::forCommand($commandName);
        }

        $serviceNameOrFQCN = $this->handlerMap[$commandName];

        if ($this->container->has($serviceNameOrFQCN)) {
            return $this->container->get($serviceNameOrFQCN);
        }

        if (class_exists($serviceNameOrFQCN)) {
            return new $serviceNameOrFQCN;
        }

        throw MissingHandlerException::forCommand($commandName);
    }

    /**
     * Checks whether the given command exists.
     *
     * @param string $commandName
     * @return bool TRUE if the given command exists, FALSE otherwise
     */
    protected function commandExists(string $commandName): bool
    {
        if (!$this->handlerMap) {
            $this->handlerMap = $this->container->get('config')['command_bus']['handler_map'];
        }

        return isset($this->handlerMap[$commandName]);
    }
}
