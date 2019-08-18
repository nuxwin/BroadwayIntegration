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

/**
 * Interface IEntity
 * @package iMSCP\Plugin\BroadWayIntegration\Domain\Model
 */
interface IEntity
{
    /**
     * Compares this Entity to another Entity.
     *
     * @param IEntity $entity
     * @return boolean TRUE if both Entity have the same identity.
     */
    public function sameIdentityAs(IEntity $entity): bool;
}
