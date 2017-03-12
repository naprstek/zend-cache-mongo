<?php

require __DIR__ . '/../vendor/autoload.php';

// this is required for cache/integration test as long they didn't update to PHPUnit 6
// see https://github.com/php-cache/integration-tests/issues/79
if (! class_exists('PHPUnit_Framework_TestCase')) {
    class_alias(PHPUnit\Framework\TestCase::class, 'PHPUnit_Framework_TestCase');
}
