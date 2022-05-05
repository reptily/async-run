# Install
```bash
composer require reptily/async-run
```

# Example use

Example file: <a href="https://github.com/reptily/async-run/blob/master/example/index.php">/example/index.php</a>

This library provides several methods for working with oscichronity.

When an object is initialized in the constructor, parameters are available.

```php
new AsyncRun(
    int $coresCount = 1,             // Count of running threads
    string $pathTmpDir = '/tmp',     // path to temporary files directory
    ?string $lockFileName = null     // filename for locale
);
```

## Methods

*init* - Object inacylization method, used for prelaunch configuration.

```php
protected function init(): void
{
    $this->arrayTest = [
        [self::FIELD_NAME => 'Bob'],
        [self::FIELD_NAME => 'Mark'],
        [self::FIELD_NAME => 'Ana'],
    ];
}
```

*getError* - Error return method.

```php
protected function getError(string $message): void
{
    print_r($message);
}
```

*done* - The method is run after the entire execution, as a rule, it serves to generate a report.

```php
protected function done(): void
{
    echo "Done in " . $this->getProgressTime() . " sec.\n";
}
```

*unlock* - Method for pre-unblocking.
```php
(new Example(2))->unlock();
```

*run* - Method to run handlers.
```php
(new Example(2))->run();
```

*workerAfterSpawn* - Method starts before each worker and distributes tasks to them.

```php
protected function workerAfterSpawn(): void
{
    next($this->arrayTest);
}
```

*getWorkerResults* - The method returns the result of the worker's work.

```php
protected function getWorkerResults(): void
{
    $item = current($this->arrayTest);
    echo $item[self::FIELD_NAME] . "\n";
}
```

*getWorkerDoneCondition* - The method serves as a pointer to the completion of processing all jobs.

```php
protected function getWorkerDoneCondition(): bool
{
    return current($this->arrayTest) === false;
}
```