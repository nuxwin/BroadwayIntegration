<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

use Broadway\EventStore\ConcurrencyConflictResolver\WhitelistConcurrencyConflictResolver;

return [
    // Event store database table
    'event_store_table'             => 'broadway_integration_event_stream',

    // Event Store - Concurrency conflict resolver configuration
    // See http://danielwhittaker.me/2014/09/29/handling-concurrency-issues-cqrs-event-sourced-system/
    // for a understanding
    'concurrency_conflict_resolver' => [
        // List of independent events
        WhitelistConcurrencyConflictResolver::class => [

        ]
    ]
];
