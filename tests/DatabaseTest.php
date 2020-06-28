<?php

/** @noinspection PhpComposerExtensionStubsInspection */

/** @noinspection PhpDeprecationInspection */

namespace TorneLIB\Module;

use Exception;
use JsonMapper_Exception;
use PHPUnit\Framework\TestCase;
use TorneLIB\Exception\Constants;
use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\Helpers\Version;
use TorneLIB\Model\Database\Drivers;
use TorneLIB\Model\Database\Ports;
use TorneLIB\Model\Database\Servers;
use TorneLIB\Model\Database\Types;
use TorneLIB\Module\Config\DatabaseConfig;
use TorneLIB\Module\Database\Drivers\MySQL;
use TorneLIB\MODULE_DATABASE;

require_once(__DIR__ . '/../vendor/autoload.php');

@unlink(__DIR__ . '/config.json');

// Initializer.
if (!file_exists(__DIR__ . '/config.json')) {
    @copy(
        __DIR__ . '/config.json.sample',
        __DIR__ . '/config.json'
    );
}

class DatabaseTest extends TestCase
{
    private $serverhost = '127.0.0.1';
    private $username = 'tornelib';
    private $password = 'tornelib1337';

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
        /** @noinspection PhpParamsInspection */
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
        static::assertEquals(Ports::MYSQL, (new MySQL())->getServerPort());
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
            $unimpl = $e->getCode() === Constants::LIB_DATABASE_NOT_IMPLEMENTED;
        }

        $db = new MODULE_DATABASE();
        $db->setServerType(Types::MYSQL);
        static::assertTrue(
            $unimpl &&
            get_class($db) === MODULE_DATABASE::class &&
            get_class($db->getHandle()) === MySQL::class
        );
    }

    /**
     * @test
     * @throws ExceptionHandler
     * @throws JsonMapper_Exception
     */
    public function getConfigStates()
    {
        $conf = (new DatabaseConfig())->getConfig(__DIR__ . '/config.json');
        $notThere = null;
        $emptyJson = null;
        try {
            (new DatabaseConfig())->getConfig('not-there');
        } catch (ExceptionHandler $e) {
            $notThere = $e->getCode();
        }

        try {
            (new DatabaseConfig())->getConfig(__DIR__ . '/empty.txt');
        } catch (ExceptionHandler $e) {
            $emptyJson = $e->getCode();
        }

        static::assertTrue(
            get_class($conf) === Servers::class &&
            $notThere === 404 &&
            $emptyJson === Constants::LIB_DATABASE_EMPTY_JSON_CONFIG
        );
    }

    /**
     * @test
     * @testdox This test requires that all drivers is installed.
     * @throws ExceptionHandler
     */
    public function forceGetDriver()
    {
        $sql = new MySQL();
        $preferred = $sql->getPreferredDriver();
        $sql->setPreferredDriver(Drivers::DRIVER_MYSQL_PDO);
        $newPreferred = $sql->getPreferredDriver();

        static::assertTrue(
            $preferred === Drivers::DRIVER_MYSQL_IMPROVED &&
            $newPreferred === Drivers::DRIVER_MYSQL_PDO
        );
    }

    /**
     * @test
     * @throws ExceptionHandler
     */
    public function connectDefault()
    {
        // Return $this instead of boolean.
        //Flag::setFlag('SQLCHAIN', true);
        $sql = (new MySQL())->connect();
        $configured = new MySQL();
        $configured->connect(
            'manual',
            null,
            $this->serverhost,
            $this->username,
            $this->password
        );
        $configured->setDatabase('tornelib_tests');
        $switched = $configured->getDatabase();

        static::assertTrue(
            $sql &&
            $switched === 'tornelib_tests'
        );
    }

    /**
     * @test
     * @throws ExceptionHandler
     * @noinspection DynamicInvocationViaScopeResolutionInspection
     * @noinspection PhpParamsInspection
     */
    public function connectMysqlIFail()
    {
        static::expectException(ExceptionHandler::class);
        (new MySQL())->connect(
            'manual',
            null,
            '127.0.0.1',
            sprintf('fail%s', sha1(uniqid('', true))),
            'tornelib1337'
        );
    }

    /**
     * @test
     * @throws ExceptionHandler
     */
    public function connectDeprecatedSuccess()
    {
        $sql = new MySQL();
        $sql->setPreferredDriver(Drivers::DRIVER_MYSQL_DEPRECATED);
        static::assertTrue($sql->connect());
    }

    /**
     * @test
     * @throws ExceptionHandler
     * @noinspection DynamicInvocationViaScopeResolutionInspection
     * @noinspection PhpParamsInspection
     */
    public function connectFailDeprecated()
    {
        static::expectException(ExceptionHandler::class);

        $sql = new MySQL();
        $sql->setPreferredDriver(Drivers::DRIVER_MYSQL_DEPRECATED);
        $sql->connect(
            null,
            null,
            '127.0.0.1',
            sprintf('fail%s', sha1(uniqid('', true))),
            'tornelib1337'
        );
    }

    /**
     * @test
     * @throws ExceptionHandler
     */
    public function connectManualSuccess()
    {
        $configured = new MySQL();
        $configured->connect(
            null,
            null,
            $this->serverhost,
            $this->username,
            $this->password
        );
        $configured->setDatabase('tornelib_tests');
    }

    /**
     * @test
     * @throws ExceptionHandler
     */
    public function connectPdo()
    {
        $sql = new MySQL();
        $sql->setPreferredDriver(Drivers::DRIVER_MYSQL_PDO);
        static::assertTrue($sql->connect());
    }

    /**
     * @test
     * @throws ExceptionHandler
     * @noinspection DynamicInvocationViaScopeResolutionInspection
     * @noinspection PhpParamsInspection
     */
    public function connectPdoFail()
    {
        static::expectException(ExceptionHandler::class);

        $sql = new MySQL();
        $sql->setPreferredDriver(Drivers::DRIVER_MYSQL_PDO);
        $sql->connect(
            null,
            null,
            '127.0.0.1',
            sprintf('fail%s', sha1(uniqid('', true))),
            'tornelib1337'
        );
    }

    /**
     * @test
     * @throws ExceptionHandler
     */
    public function connectDeprecatedModule()
    {
        $sql = new MODULE_DATABASE();
        $sql->setServerType(Types::MYSQL);
        $sql->setPreferredDriver(Drivers::DRIVER_MYSQL_PDO);
        static::assertTrue(
            $sql->connect(
                null,
                null,
                $this->serverhost,
                $this->username,
                $this->password
            )
        );
    }

    /**
     * Configurations.
     * @throws ExceptionHandler
     * @throws JsonMapper_Exception
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function getConfig()
    {
        $conf = (new DatabaseConfig())->getConfig(__DIR__ . '/config.json');
        $localhostConfigurationData = $conf->getServer('localhost');

        static::assertTrue(
            get_class($conf) === Servers::class &&
            $localhostConfigurationData->getPassword() === 'tornelib1337'
        );
    }
}
