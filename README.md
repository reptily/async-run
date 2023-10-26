# Install
```bash
composer require reptily/async-run
```

### Example use as object

Example file: <a href="https://github.com/reptily/async-run/blob/master/example/async_run.php">/example/async_run.php</a>

This library provides several methods for working with asynchronicity.

When an object is initialized in the constructor, parameters are available.

```php
new AsyncRun(
    int $coresCount = 1,             // Count of running threads
    string $pathTmpDir = '/tmp',     // path to temporary files directory
    ?string $lockFileName = null     // filename for locale
);
```

### Example use as callbacks

Example file: <a href="https://github.com/reptily/async-run/blob/master/example/async.php">/example/async.php</a>

You can easily use the library within your code.

To do this, fill in the necessary functions in the **run(...function)** method

After this, all the functions specified in run() will be executed,
if there is success, **then()** will be called,
if there is an error, **catch()** will be called,
the **finally()** method will be called in any of the above cases.

```php
Async::run(
    function () {
        sleep(1);
        echo "AAA" . PHP_EOL;
    },
    function () {
        echo "BBB" . PHP_EOL;
    }
)->then(function () {
    echo "CCC" . PHP_EOL;
})->finally(function () {
    echo "DDD" . PHP_EOL;
})->catch(function ($errorText) {
    echo "Error " . $errorText . PHP_EOL;
});
```
### Methods

*init* - Object initialization method, used for prelaunch configuration.

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