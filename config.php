<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

return [
    // Container configuration
    'container'   => include __DIR__ . '/config/container.php',

    // Command bus configuration
    'command_bus' => include __DIR__ . '/config/command_bus.php',

    // Event bus configuration
    'event_bus'   => include __DIR__ . '/config/event_bus.php',

    // Persistence configuration
    'persistence' => include __DIR__ . '/config/persistence.php',
];
