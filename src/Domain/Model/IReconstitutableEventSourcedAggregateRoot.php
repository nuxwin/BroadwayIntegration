<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

declare(strict_types=1);

namespace iMSCP\Plugin\BroadWayIntegration\Domain\Model;

use Broadway\EventSourcing\EventSourcedAggregateRoot;

/**
 * Interface IReconstitutableEventSourcedAggregateRoot
 * @package iMSCP\Plugin\BroadWayIntegration\Domain\Model
 */
interface IReconstitutableEventSourcedAggregateRoot
{
    /**
     * Instantiate the event sourced root aggregate for reconstitution.
     *
     * @return EventSourcedAggregateRoot
     */
    public static function instantiateForReconstitution(): EventSourcedAggregateRoot;
}
