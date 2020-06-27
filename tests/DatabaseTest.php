<?php

namespace TorneLIB\Module;

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../vendor/autoload.php');

class DatabaseTest extends TestCase
{
    /**
     * @test
     */
    public function initializer()
    {
        static::assertInstanceOf(
            Database::class,
            (new Database())
        );
    }
}
