<?php

require __DIR__.'/../vendor/autoload.php';

use Reptily\AsyncRun\AsyncRun;

class Example extends AsyncRun
{
    private const FIELD_NAME = 'name';
    private array $arrayTest;

    protected function workerAfterSpawn(): void
    {
        next($this->arrayTest);
    }

    protected function getWorkerResults(): void
    {
        $item = current($this->arrayTest);

        // Special delay for checking asynchrony.
        if ($item[self::FIELD_NAME] === 'Bob') {
            sleep(1);
        }

        echo $item[self::FIELD_NAME] . "\n";
    }

    protected function getWorkerDoneCondition(): bool
    {
        return current($this->arrayTest) === false;
    }

    protected function getError(string $message): void
    {
        print_r($message);
    }

    protected function init(): void
    {
        $this->arrayTest = [
            [self::FIELD_NAME => 'Bob'],
            [self::FIELD_NAME => 'Mark'],
            [self::FIELD_NAME => 'Ana'],
        ];
    }

    protected function done(): void
    {
        echo "Done in " . $this->getProgressTime() . " sec.\n";
    }
}

(new Example(2))->unlock()->run();