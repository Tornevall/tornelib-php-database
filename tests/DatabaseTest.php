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
        $SQL = (new MySQL());
        static::assertEquals(
            'theIdentifier',
            $SQL->setIdentifier('theIdentifier')->getIdentifier()
        );

        $identifiers = $SQL->getConfig()->getIdentifiers();
        static::assertCount(1, $identifiers);
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

    /**
     * @test
     */
    public function setServerPort()
    {
        static::assertEquals('3300', (new MySQL())->setServerPort('3300')->getServerPort());
    }

    /**
     * @test
     */
    public function getDefaultServerPort()
    {
        static::assertEquals(3306, (new MySQL())->getServerPort());
    }

    /**
     * @test
     */
    public function setServerHost()
    {
        static::assertEquals('la-cool-host', (new MySQL())->setServerHost('la-cool-host')->getServerHost());
    }

    /**
     * @test
     */
    public function getDefaultServerHost()
    {
        static::assertEquals('127.0.0.1', (new MySQL())->getServerHost());
    }
}
