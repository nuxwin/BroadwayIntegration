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

use Psr\Container\ContainerInterface;

/**
 * Class PsrContainerHandlerLocatorFactory
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus\Locator
 */
class PsrContainerHandlerLocatorFactory
{
    /**
     * @param ContainerInterface $container
     * @return PsrContainerHandlerLocator
     */
    public function __invoke(
        ContainerInterface $container
    ): PsrContainerHandlerLocator
    {
        return new PsrContainerHandlerLocator($container);
    }
}
