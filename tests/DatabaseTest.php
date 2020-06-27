<?php

namespace TorneLIB\Module;

use Exception;
use PHPUnit\Framework\TestCase;
use TorneLIB\Helpers\Version;

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

    /**
     * @test
     * @throws Exception
     */
    public function theVersion()
    {
        static::expectException(Exception::class);

        Version::getRequiredVersion('9999');
    }
}
