<?php

require __DIR__.'/../vendor/autoload.php';

use Reptily\AsyncRun\Async;

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