<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

declare(strict_types=1);

namespace iMSCP\Plugin\BroadWayIntegration\Application\CommandBus;

use iMSCP\Plugin\BroadWayIntegration\Application\Command\ICommand;

/**
 * Interface ICommandBus
 * @package iMSCP\Plugin\BroadWayIntegration\Application\CommandBus
 */
interface ICommandBus
{
    /**
     * Handles an ICommand.
     *
     * @param ICommand $command
     * @return void
     */
    public function handle(ICommand $command): void;
}
