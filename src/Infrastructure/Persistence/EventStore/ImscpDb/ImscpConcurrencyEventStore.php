<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

/**
 * @noinspection PhpUnhandledExceptionInspection PhpDocMissingThrowsInspection
 */

declare(strict_types=1);

namespace iMSCP\Plugin\BroadWayIntegration\Infrastructure\Persistence\EventStore\ImscpDb;

use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\ConcurrencyConflictResolver\ConcurrencyConflictResolver;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventVisitor;
use Broadway\EventStore\Exception\DuplicatePlayheadException;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\EventStoreManagement;

/**
 * Class ImscpConcurrencyEventStore
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\Persistence\EventStore\ImscpDb
 */
class ImscpConcurrencyEventStore implements EventStore, EventStoreManagement
{
    /**
     * @var ImscpDbEventStore
     */
    private $eventStore;

    /**
     * @var ConcurrencyConflictResolver
     */
    private $conflictResolver;

    /**
     * ImscpConcurrencyEventStore constructor.
     *
     * @param ImscpDbEventStore $eventStore
     * @param ConcurrencyConflictResolver $conflictResolver
     */
    public function __construct(
        ImscpDbEventStore $eventStore,
        ConcurrencyConflictResolver $conflictResolver
    )
    {
        $this->eventStore = $eventStore;
        $this->conflictResolver = $conflictResolver;
    }

    /**
     * @inheritDoc
     */
    public function append($id, DomainEventStream $uncommittedEvents): void
    {
        try {
            $this->eventStore->append($id, $uncommittedEvents);
        } catch (DuplicatePlayheadException $e) {
            $committedEvents = $this->eventStore->load($id);
            $conflictingEvents = $this->getConflictingEvents(
                $uncommittedEvents, $committedEvents
            );

            $conflictResolvedEvents = [];
            $playhead = $this->getCurrentPlayhead($committedEvents);

            /** @var DomainMessage $uncommittedEvent */
            foreach ($uncommittedEvents as $uncommittedEvent) {
                foreach ($conflictingEvents as $conflictingEvent) {
                    if ($this->conflictResolver->conflictsWith(
                        $conflictingEvent, $uncommittedEvent
                    )) {
                        throw $e;
                    }
                }

                ++$playhead;

                $conflictResolvedEvents[] = new DomainMessage(
                    $id,
                    $playhead,
                    $uncommittedEvent->getMetadata(),
                    $uncommittedEvent->getPayload(),
                    $uncommittedEvent->getRecordedOn()
                );
            }

            $this->append($id, new DomainEventStream($conflictResolvedEvents));
        }
    }

    /**
     * @param DomainEventStream $uncommittedEvents
     * @param DomainEventStream $committedEvents
     * @return array
     */
    private function getConflictingEvents(
        DomainEventStream $uncommittedEvents,
        DomainEventStream $committedEvents
    ): array
    {
        $conflictingEvents = [];

        /** @var DomainMessage $committedEvent */
        foreach ($committedEvents as $committedEvent) {
            /** @var DomainMessage $uncommittedEvent */
            foreach ($uncommittedEvents as $uncommittedEvent) {
                if ($committedEvent->getPlayhead()
                    >= $uncommittedEvent->getPlayhead()
                ) {
                    $conflictingEvents[] = $committedEvent;

                    break;
                }
            }
        }

        return $conflictingEvents;
    }

    /**
     * @param DomainEventStream $committedEvents
     * @return int
     */
    private function getCurrentPlayhead(DomainEventStream $committedEvents): int
    {
        $events = iterator_to_array($committedEvents);
        /** @var DomainMessage $lastEvent */
        $lastEvent = end($events);
        $playhead = $lastEvent->getPlayhead();

        return $playhead;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id): DomainEventStream
    {
        return $this->eventStore->load($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPlayhead($id, int $playhead): DomainEventStream
    {
        return $this->eventStore->loadFromPlayhead($id, $playhead);
    }

    /**
     * {@inheritdoc}
     */
    public function visitEvents(
        Criteria $criteria, EventVisitor $eventVisitor
    ): void
    {
        $this->eventStore->visitEvents($criteria, $eventVisitor);
    }
}
