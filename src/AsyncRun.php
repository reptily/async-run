<?php

namespace Reptily\AsyncRun;

abstract class AsyncRun
{
    protected float $timeStart;
    private string $pathTmpDir;
    private string $lockFileName;
    private string $pathSocket;
    private string $pathLockFile;
    private $socket;
    private int $coresCount;

    public function __construct(int $coresCount = 1, string $pathTmpDir = '/tmp', ?string $lockFileName = null)
    {
        $this->setPathTmpDir($pathTmpDir);
        $this->setLockFileName($lockFileName === null ? get_class() . '.lock' : $lockFileName);
        $this->setPathLockFile($pathTmpDir . DIRECTORY_SEPARATOR . $this->lockFileName);
        $this->setPathSocket($pathTmpDir . DIRECTORY_SEPARATOR . get_class() . '.sock');
        $this->setCoresCount($coresCount);
    }

    public function getTimeStart(): float
    {
        return $this->timeStart;
    }

    private function setTimeStart(float $timeStart): void
    {
        $this->timeStart = $timeStart;
    }

    public function getPathTmpDir(): string
    {
        return $this->pathTmpDir;
    }

    private function setPathTmpDir(string $pathTmpDir): void
    {
        $this->pathTmpDir = $pathTmpDir;
    }

    public function getLockFileName(): ?string
    {
        return $this->lockFileName;
    }

    private function setLockFileName(?string $lockFileName): void
    {
        $this->lockFileName = $lockFileName;
    }

    public function getPathSocket(): string
    {
        return $this->pathSocket;
    }

    private function setPathSocket(string $pathSocket): void
    {
        $this->pathSocket = $pathSocket;
    }

    public function getPathLockFile(): string
    {
        return $this->pathLockFile;
    }

    private function setPathLockFile(string $pathLockFile): void
    {
        $this->pathLockFile = $pathLockFile;
    }

    private function getSocket()
    {
        return $this->socket;
    }

    private function setSocket($socket): void
    {
        $this->socket = $socket;
    }

    public function getCoresCount(): int
    {
        return $this->coresCount;
    }

    private function setCoresCount(int $coresCount): void
    {
        $this->coresCount = $coresCount;
    }

    public function run()
    {
        $this->setTimeStart(microtime(true));
        $coresCount = $this->getCoresCount() ?? $this->getTotalCpuCores();
        $this->initSocket();
        $this->init();

        $processing = new MainCycle(
            function () {
                $this->getWorkerResults();
            },
            function () {
                $this->socketHandler();
                return $this->getWorkerDoneCondition();
            }
        );

        $processing
            ->setMaxChildren($coresCount)
            ->setAfterSpawnWorker(function () {
                $this->workerAfterSpawn();
            })
            ->run(false);

        $this->done();
        $this->deleteLockFile();
        $this->shutdownSocket();
    }

    private function deleteLockFile(): void
    {
        if (is_file($this->getLockFileName())) {
            unlink($this->getLockFileName());
        }
    }

    public function unlock(): self
    {
        $this->deleteLockFile();
        $this->deleteSockFile();

        return $this;
    }

    private function deleteSockFile(): void
    {
        if (touch($this->getPathSocket())) {
            unlink($this->getPathSocket());
        }
    }

    private function initSocket()
    {
        $this->setSocket(socket_create(AF_UNIX, SOCK_STREAM, 0));
        if (false === $this->socket) {
            $this->socketError();
        }

        $success = @socket_set_option($this->getSocket(), SOL_SOCKET, SO_REUSEADDR, 1)
            && @socket_bind($this->getSocket(), $this->getPathSocket())
            && @socket_listen($this->getSocket(), SOMAXCONN)
            && @socket_set_nonblock($this->getSocket());

        if (!$success) {
            $this->socketError($this->getSocket());
        }
    }

    private function socketError($socket = null): void
    {
        $message = socket_strerror(socket_last_error($socket));
        $this->getError($message);
    }

    private function shutdownSocket()
    {
        if (is_resource($this->getSocket())) {
            socket_shutdown($this->getSocket());
        }

        $this->deleteSockFile();
    }

    private function socketHandler(): void
    {
        $socket = socket_accept($this->getSocket());
        if (is_resource($socket)) {
            do {
                $buffer = socket_read($socket, 1024, PHP_BINARY_READ);
                $repeat = $buffer !== false && strlen($buffer) > 0;
            } while ($repeat);
        }
    }

    private function getTotalCpuCores() {
        return (int) ((PHP_OS_FAMILY == 'Windows') ? (getenv("NUMBER_OF_PROCESSORS"))
            : substr_count(file_get_contents("/proc/cpuinfo"),"processor"));
    }

    public function getProgressTime(): float
    {
        return microtime(true) - $this->getTimeStart();
    }

    abstract protected function workerAfterSpawn(): void;

    abstract protected function getWorkerResults(): void;

    abstract protected function getWorkerDoneCondition(): bool;

    abstract protected function getError(string $message): void;

    abstract protected function init(): void;

    abstract protected function done(): void;
}
