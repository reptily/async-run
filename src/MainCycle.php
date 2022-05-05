<?php

namespace Reptily\AsyncRun;

use Closure;

class MainCycle extends Fork
{
    private int $maxChildren = 2;
    private Closure $worker;
    private Closure $whenDone;
    private ?Closure $afterSpawnWorker = null;
    private ?Closure $afterCompleteWorker = null;

    public function __construct(
        Closure $worker,
        Closure $whenDone
    ) {
        $this->worker = $worker;
        $this->whenDone = $whenDone;
    }

    public function run(bool $dieAfterForkComplete = false): void
    {
        $canRun = true;
        $whenDone = $this->whenDone;
        if ($whenDone()) {
            return;
        }
        while ($canRun) {
            if ($this->cntWorkers < $this->maxChildren && !$whenDone()) {
                if ($this->spawn($this->worker, $dieAfterForkComplete) > 0) {
                    if (null !== $this->afterSpawnWorker) {
                        $hook = $this->afterSpawnWorker;
                        $hook();
                    }
                }
            }

            if ($this->await() > 0) {
                if (null !== $this->afterCompleteWorker) {
                    $hook = $this->afterCompleteWorker;
                    $hook();
                }
                if ($whenDone() && $this->cntWorkers < 1) {
                    $canRun = false;
                }
            }
        }
    }

    public function getWorker(): Closure
    {
        return $this->worker;
    }

    public function setWorker(Closure $worker): MainCycle
    {
        $this->worker = $worker;
        return $this;
    }

    public function getWhenDone(): Closure
    {
        return $this->whenDone;
    }

    public function setWhenDone(Closure $whenDone): MainCycle
    {
        $this->whenDone = $whenDone;
        return $this;
    }

    public function getAfterSpawnWorker(): ?Closure
    {
        return $this->afterSpawnWorker;
    }

    public function setAfterSpawnWorker(?Closure $afterSpawnWorker): MainCycle
    {
        $this->afterSpawnWorker = $afterSpawnWorker;
        return $this;
    }

    public function getMaxChildren(): int
    {
        return $this->maxChildren;
    }

    public function setMaxChildren(int $maxChildren): MainCycle
    {
        $this->maxChildren = $maxChildren;
        return $this;
    }

    public function getAfterCompleteWorker(): ?Closure
    {
        return $this->afterCompleteWorker;
    }

    public function setAfterCompleteWorker(?Closure $afterCompleteWorker): MainCycle
    {
        $this->afterCompleteWorker = $afterCompleteWorker;
        return $this;
    }
}
