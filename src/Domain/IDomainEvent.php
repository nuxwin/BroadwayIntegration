<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

declare(strict_types=1);

namespace iMSCP\Plugin\BroadWayIntegration\Domain;

use Broadway\Serializer\Serializable;

/**
 * Interface DomainEvent
 * @package iMSCP\Plugin\BroadWayIntegration\Domain
 */
interface IDomainEvent extends Serializable
{

}
