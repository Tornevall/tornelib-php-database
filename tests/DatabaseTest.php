<?php

namespace TorneLIB\Module;

use Exception;
use PHPUnit\Framework\TestCase;
use TorneLIB\Helpers\Version;

require_once(__DIR__ . '/../vendor/autoload.php');

// Initializer.
if (!file_exists(__DIR__ . '/config.json')) {
    copy(
        __DIR__ . '/config.json.sample',
        __DIR__ . '/config.json'
    );
}

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
     * @noinspection DynamicInvocationViaScopeResolutionInspection
     */
    public function theVersion()
    {
        static::expectException(Exception::class);

        Version::getRequiredVersion('9999');
    }
}
