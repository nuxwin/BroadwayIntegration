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
 * Interface IValueObject
 * @package iMSCP\Plugin\BroadWayIntegration\Domain\Model
 */
interface IValueObject
{
    /**
     * Compares this value object to another value object.
     *
     * @param IValueObject $valueObject
     * @return boolean TRUE if both value objects have the same type and value.
     */
    public function sameValueAs(IValueObject $valueObject);
}
