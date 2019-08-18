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

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventStreamNotFoundException;
use Broadway\EventStore\EventVisitor;
use Broadway\EventStore\Exception\DuplicatePlayheadException;
use Broadway\EventStore\Management\Criteria;
use Broadway\EventStore\Management\CriteriaNotSupportedException;
use Broadway\EventStore\Management\EventStoreManagement;
use Broadway\Serializer\Serializer;
use iMSCP_Database;
use PDO;
use PDOException;
use PDOStatement;
use Throwable;

/**
 * Class ImscpDbEventStore
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\Persistence\EventStore\ImscpDb
 */
class ImscpDbEventStore implements EventStore, EventStoreManagement
{
    /**
     * @var iMSCP_Database
     */
    private $db;

    /**
     * @var Serializer
     */
    private $payloadSerializer;

    /**
     * @var Serializer
     */
    private $metadataSerializer;

    /**
     * @var PDOStatement
     */
    private $loadStatement;

    /**
     * @var string
     */
    private $tableName;

    /**
     * ImscpDbEventStore constructor.
     *
     * @param iMSCP_Database $db
     * @param Serializer $payloadSerializer
     * @param Serializer $metadataSerializer
     * @param string $tableName
     */
    public function __construct(
        iMSCP_Database $db,
        Serializer $payloadSerializer,
        Serializer $metadataSerializer,
        string $tableName
    )
    {
        $this->db = $db;
        $this->payloadSerializer = $payloadSerializer;
        $this->metadataSerializer = $metadataSerializer;
        $this->tableName = $tableName;
    }

    /**
     * @inheritDoc
     */
    public function load($id): DomainEventStream
    {
        $stmt = $this->prepareLoadStatement();
        $stmt->execute([$id, 0]);

        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->deserializeEvent($row);
        }

        if (empty($events)) {
            throw new EventStreamNotFoundException(sprintf(
                'EventStream not found for aggregate with id %s for table %s',
                $id,
                $this->tableName
            ));
        }

        return new DomainEventStream($events);
    }

    /**
     * Prepares and return load statement.
     *
     * @return false|PDOStatement|null
     */
    private function prepareLoadStatement(): PDOStatement
    {
        if (NULL === $this->loadStatement) {
            try {
                $this->loadStatement = $this->db->prepare(
                    "
                    SELECT `uuid`, `playhead`, `metadata`, `payload`,
                        `recorded_on`
                    FROM `{$this->tableName}`
                    WHERE `uuid` = ?
                    AND `playhead` >= ?
                    ORDER BY `playhead` ASC
                "
                );
            } catch (Throwable $e) {
                throw ImscpDbEventStoreException::create($e);
            }
        }

        return $this->loadStatement;
    }

    /**
     * Deserialize the given event from event stream.
     *
     * @param array $row
     * @return DomainMessage
     */
    private function deserializeEvent(array $row): DomainMessage
    {
        return new DomainMessage(
            $row['uuid'],
            (int)$row['playhead'],
            $this->metadataSerializer->deserialize(
                json_decode($row['metadata'], true)
            ),
            $this->payloadSerializer->deserialize(
                json_decode($row['payload'], true)
            ),
            DateTime::fromString($row['recorded_on'])
        );
    }

    /**
     * @inheritDoc
     */
    public function loadFromPlayhead($id, int $playhead): DomainEventStream
    {
        $stmt = $this->prepareLoadStatement();
        $stmt->execute([$id, $playhead]);

        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->deserializeEvent($row);
        }

        return new DomainEventStream($events);
    }

    /**
     * @inheritDoc
     */
    public function append($id, DomainEventStream $eventStream): void
    {
        $this->db->beginTransaction();

        try {
            foreach ($eventStream as $domainMessage) {
                $this->insertMessage($this->db, $domainMessage);
            }

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();

            if ($e->getCode() == 23000) {
                throw new DuplicatePlayheadException($eventStream, $e);
            }

            throw ImscpDbEventStoreException::create($e);
        }
    }

    /**
     * Insert the given message in event stream.
     *
     * @param iMSCP_Database $db
     * @param DomainMessage $domainMessage
     */
    private function insertMessage(
        iMSCP_Database $db,
        DomainMessage $domainMessage
    ): void
    {
        $stmt = $db->prepare(
            "
                INSERT INTO `{$this->tableName}` (
                    `uuid`, `playhead`, `metadata`, `payload`, `recorded_on`,
                    `type`
                ) VALUES (
                    ?, ?, ?, ?, ?, ?
                )
           "
        );
        $stmt->execute(
            [
                (string)$domainMessage->getId(),
                $domainMessage->getPlayhead(),
                json_encode($this->metadataSerializer->serialize(
                    $domainMessage->getMetadata()
                )),
                json_encode($this->payloadSerializer->serialize(
                    $domainMessage->getPayload()
                )),
                $domainMessage->getRecordedOn()->toString(),
                $domainMessage->getType()
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function visitEvents(
        Criteria $criteria,
        EventVisitor $eventVisitor
    ): void
    {
        $stmt = $this->prepareVisitEventsStatement($criteria);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $domainMessage = $this->deserializeEvent($row);
            $eventVisitor->doWithEvent($domainMessage);
        }
    }

    /**
     * @param Criteria $criteria
     * @return PDOStatement
     */
    private function prepareVisitEventsStatement(Criteria $criteria)
    {
        $where = $this->prepareVisitEventsStatementWhere($criteria);

        try {
            $stmt = $this->db->prepare(
                "
                    SELECT `uuid`, `playhead`, `metadata`, `payload`,
                        `recorded_on`
                    FROM {$this->tableName}
                    $where
                    ORDER BY id ASC
                "
            );
        } catch (Throwable $e) {
            throw ImscpDbEventStoreException::create($e);
        }

        return $stmt;
    }

    /**
     * @param Criteria $criteria
     * @return string
     */
    private function prepareVisitEventsStatementWhere(
        Criteria $criteria
    ): string
    {
        if ($criteria->getAggregateRootTypes()) {
            throw new CriteriaNotSupportedException(
                'Cannot support criteria based on aggregate root types.'
            );
        }

        $criteriaTypes = [];

        if ($criteria->getAggregateRootIds()) {
            $criteriaTypes[] = '`uuid` IN ('
                . join(
                    ',',
                    array_map(
                        [$this->db, 'quote'], $criteria->getAggregateRootIds()
                    )
                )
                . ')';
        }

        if ($criteria->getEventTypes()) {
            $criteriaTypes[] = '`type` IN ('
                . join(
                    ',',
                    array_map([$this->db, 'quote'], $criteria->getEventTypes())
                )
                . ')';
        }

        if (!$criteriaTypes) {
            return '';
        }

        $where = 'WHERE ' . join(' AND ', $criteriaTypes);

        return $where;
    }
}
