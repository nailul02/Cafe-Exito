<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var list<callable> */
    private array $errorHandlersBeforeLaravel = [];

    /** @var list<callable> */
    private array $exceptionHandlersBeforeLaravel = [];

    protected function setUp(): void
    {
        $this->errorHandlersBeforeLaravel = $this->snapshotHandlers(
            static fn (callable $handler) => set_error_handler($handler),
            static fn () => restore_error_handler(),
            static fn () => set_error_handler(static fn () => false),
        );
        $this->exceptionHandlersBeforeLaravel = $this->snapshotHandlers(
            static fn (callable $handler) => set_exception_handler($handler),
            static fn () => restore_exception_handler(),
            static fn () => set_exception_handler(static fn (Throwable $exception) => null),
        );

        parent::setUp();
    }

    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } finally {
            $this->restoreHandlerStack(
                $this->errorHandlersBeforeLaravel,
                static fn () => set_error_handler(static fn () => false),
                static fn () => restore_error_handler(),
                static fn (callable $handler) => set_error_handler($handler),
            );
            $this->restoreHandlerStack(
                $this->exceptionHandlersBeforeLaravel,
                static fn () => set_exception_handler(static fn (Throwable $exception) => null),
                static fn () => restore_exception_handler(),
                static fn (callable $handler) => set_exception_handler($handler),
            );
        }
    }

    /**
     * Read the active PHP handler stack without leaving the temporary probe installed.
     * The returned list is ordered from the oldest handler to the newest.
     *
     * @return list<callable>
     */
    private function snapshotHandlers(callable $push, callable $pop, callable $probe): array
    {
        $handlers = [];

        while (true) {
            $previous = $probe();
            $pop();

            if ($previous === null) {
                break;
            }

            $handlers[] = $previous;
            $pop();
        }

        $handlers = array_reverse($handlers);
        foreach ($handlers as $handler) {
            $push($handler);
        }

        return $handlers;
    }

    /** @param list<callable> $handlers */
    private function restoreHandlerStack(array $handlers, callable $probe, callable $pop, callable $push): void
    {
        while (true) {
            $previous = $probe();
            $pop();

            if ($previous === null) {
                break;
            }

            $pop();
        }

        foreach ($handlers as $handler) {
            $push($handler);
        }
    }
}
