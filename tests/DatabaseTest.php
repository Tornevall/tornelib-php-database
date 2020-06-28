<?php

namespace TorneLIB\Module;

use Exception;
use PHPUnit\Framework\TestCase;
use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\Helpers\Version;
use TorneLIB\Module\Config\DatabaseConfig;
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

    /**
     * @test
     * @throws ExceptionHandler
     */
    public function setDbIdentifier()
    {
        $fail = false;
        $first = (new DatabaseConfig())->setDatabase('tests', 'test')->getDatabase('test');
        $second = (new DatabaseConfig())->setDatabase('tests')->getDatabase();
        try {
            // If using something else than the default identifier, requesting database name will fail.
            (new DatabaseConfig())->setDatabase('tests', 'test')->getDatabase();
        } catch (ExceptionHandler $e) {
            $fail = true;
        }

        static::assertTrue(
            $first === 'tests' &&
            $second === 'tests' &&
            $fail
        );
    }
}
