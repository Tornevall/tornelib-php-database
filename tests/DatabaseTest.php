<?php

namespace TorneLIB\Module;

use Exception;
use PHPUnit\Framework\TestCase;
use TorneLIB\Exception\Constants;
use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\Helpers\Version;
use TorneLIB\Model\Database\Types;
use TorneLIB\Module\Config\DatabaseConfig;
use TorneLIB\Module\Database\Drivers\MySQL;
use TorneLIB\MODULE_DATABASE;

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

    /**
     * @test
     */
    public function setServerUser()
    {
        static::assertEquals('root', (new MySQL())->setServerUser('root')->getServerUser());
    }

    /**
     * @test
     */
    public function setServerPassword()
    {
        static::assertEquals('covid-19', (new MySQL())->setServerPassword('covid-19')->getServerPassword());
    }

    /**
     * @test
     */
    public function setServerUserByIdentifier()
    {
        static::assertEquals(
            'kalle',
            (new MySQL())->setIdentifier('irregular')
                ->setServerUser('kalle', 'irregular')
                ->getServerUser('irregular')
        );
    }

    /**
     * @test
     */
    public function getDefaultServerUser()
    {
        static::assertEquals(null, (new MySQL())->getServerUser());
    }

    /**
     * @test
     */
    public function getDefaultServerType()
    {
        static::assertEquals(Types::MYSQL, (new MySQL())->getServerType());
    }

    /**
     * @test
     */
    public function getMssqlServerType()
    {
        static::assertEquals(Types::MSSQL, (new MySQL())->setServerType(Types::MSSQL)->getServerType());
    }

    /**
     * @test
     * @throws ExceptionHandler
     */
    public function deprecatedCall()
    {
        $unimpl = false;
        try {
            (new MODULE_DATABASE())->setServerType(Types::NOT_IMPLEMENTED)->getServerType();
        } catch (ExceptionHandler $e) {
            $unimpl = $e->getCode() === Constants::LIB_DATABASE_NOT_IMPLEMENTED ? true : false;
        }

        $db = new MODULE_DATABASE();
        $db->setServerType(Types::MYSQL);
        static::assertTrue(
            $unimpl &&
            get_class($db) === MODULE_DATABASE::class &&
            get_class($db->getHandle()) === MySQL::class
        );
    }
}
