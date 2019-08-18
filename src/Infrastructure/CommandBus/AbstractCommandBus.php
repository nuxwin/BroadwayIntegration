<?php
/**
 * i-MSCP BroadWayIntegration plugin
 *
 * @author        Laurent Declercq <l.declercq@nuxwin.com>
 * @copyright (C) 2019 Laurent Declercq <l.declercq@nuxwin.com>
 * @license       i-MSCP License <https://www.i-mscp.net/license-agreement.html>
 */

declare(strict_types=1);

namespace iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus;

use iMSCP\Plugin\BroadWayIntegration\Application\Command\ICommand;
use iMSCP\Plugin\BroadWayIntegration\Application\CommandBus\ICommandBus;
use League\Tactician\Exception\InvalidCommandException;
use League\Tactician\Exception\InvalidMiddlewareException;
use League\Tactician\Middleware;

/**
 * Class AbstractCommandBus
 * @package iMSCP\Plugin\BroadWayIntegration\Infrastructure\CommandBus
 */
abstract class AbstractCommandBus implements ICommandBus
{
    /**
     * @var callable
     */
    private $middlewareChain;

    /**
     * @param Middleware[] $middleware
     */
    public function __construct(array $middleware)
    {
        $this->middlewareChain = $this->createExecutionChain($middleware);
    }

    /**
     * @param Middleware[] $middlewareList
     * @return callable
     */
    private function createExecutionChain($middlewareList): Callable
    {
        $lastCallable = function () {
            // the final callable is a no-op
        };

        while ($middleware = array_pop($middlewareList)) {
            if (!$middleware instanceof Middleware) {
                throw InvalidMiddlewareException::forMiddleware($middleware);
            }

            $lastCallable = function ($command) use ($middleware, $lastCallable) {
                return $middleware->execute($command, $lastCallable);
            };
        }

        return $lastCallable;
    }

    /**
     * Executes the given command and optionally returns a value
     *
     * @param ICommand $command
     * @return void
     */
    public function handle(ICommand $command): void
    {
        if (!is_object($command)) {
            throw InvalidCommandException::forUnknownValue($command);
        }

        $middlewareChain = $this->middlewareChain;
        $middlewareChain($command);
    }
}
