<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventSourcing;

/**
 * Interface representing event sourced entities.
 */
interface EventSourcedEntity
{
    /**
     * Recursively handles $event.
     *
     * @param mixed $event
     */
    public function handleRecursively($event): void;

    /**
     * Registers aggregateRoot as this EventSourcedEntity's aggregate root.
     *
     * @throws AggregateRootAlreadyRegisteredException
     */
    public function registerAggregateRoot(EventSourcedAggregateRoot $aggregateRoot): void;
}
