<?php

namespace Reptily\AsyncRun;

class Async
{
    public static function run(...$callbacks): AsyncResponse
    {
        $async = new class($callbacks) extends AsyncRun {
            public bool $isError = false;
            public ?string $errorText = null;

            public function __construct(private array $callbacks)
            {
                parent::__construct(count($this->callbacks));
            }

            protected function workerAfterSpawn(): void
            {
                next($this->callbacks);
            }

            protected function getWorkerResults(): void
            {
                current($this->callbacks)();
            }

            protected function getWorkerDoneCondition(): bool
            {
                return current($this->callbacks) === false;
            }

            protected function getError(string $message): void
            {
                $this->isError = true;
                $this->errorText = $message;
            }

            protected function init(): void
            {
                // Nothing
            }

            protected function done(): void
            {
                // Nothing
            }
        };

        $async->unlock()->run();

        return new AsyncResponse($async->isError, $async->errorText);
    }
}