<?php

namespace Reptily\AsyncRun;

class Fork
{
    protected int $cntWorkers = 0;

    public function spawn(\Closure $closure, bool $die = false, ...$params): int
    {
        if (!function_exists('pcntl_fork')) {
            throw new \Exception('PCNTL functions not available on this PHP installation');
        }

        $pid = (int) \pcntl_fork();
        if (0 == $pid) {
            $closure->__invoke(...$params);
            if ($die) {
                die;
            }
            exit;
        }
        ++$this->cntWorkers;
        return $pid;
    }

    public function await(): int
    {
        while ($signal = pcntl_waitpid(-1, $status, WNOHANG)) {
            if (-1 == $signal) {
                return $signal;
            }
            if ($signal > 0) {
                --$this->cntWorkers;
                return $signal;
            }
        }
        return $signal;
    }

    public function getCntWorkers(): int
    {
        return $this->cntWorkers;
    }
}
