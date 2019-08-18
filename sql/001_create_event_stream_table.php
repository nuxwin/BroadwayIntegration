<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

$config = iMSCP_Registry::get('pluginManager')
    ->pluginGet('BroadwayIntegration')
    ->getContainer()
    ->get('config')['persistence'];

return [
    'up'   => "
        create table if not exists `{$config['event_store_table']}` (
            `id` integer(11) unsigned auto_increment NOT NULL,
            `uuid` varchar(36) not null,
            `playhead` integer(11) unsigned not null,
            `payload` text not null,
            `metadata` text not null,
            `recorded_on` varchar(32),
            `type` varchar(255),
            primary key (`id`),
            unique key `uuid_playhead` (`uuid`,`playhead`)
        ) engine=InnoDB default charset=utf8mb4 collate=utf8mb4_unicode_ci auto_increment=1;
    ",
    'down' => "
        DROP TABLE IF EXISTS `{$config['event_store_table']}`;
    "
];
