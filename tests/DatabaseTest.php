<?php

namespace TorneLIB\Module;

use Exception;
use PHPUnit\Framework\TestCase;
use TorneLIB\Helpers\Version;
use TorneLIB\Module\Database\Drivers\MySQL;

require_once(__DIR__ . '/../vendor/autoload.php');

// Initializer.
if (!file_exists(__DIR__ . '/config.json')) {
    @copy(
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
            MySQL::class,
            (new MySQL())
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

    /**
     * @test
     */
    public function setIdentifier()
    {
        static::assertEquals(
            'theIdentifier',
            (new MySQL())->setIdentifier('theIdentifier')->getIdentifier()
        );
    }
}
