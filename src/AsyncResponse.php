<?php

namespace Reptily\AsyncRun;

class AsyncResponse
{
    public function __construct(private bool $isError, private ?string $errorText)
    {
    }

    public function then($callback): self
    {
        if (!$this->isError) {
            $callback(null);
        }

        return $this;
    }

    public function catch($callback): self
    {
        if ($this->isError) {
            $callback($this->errorText);
        }

        return $this;
    }

    public function finally($callback): self
    {
        $callback(null);

        return $this;
    }
}