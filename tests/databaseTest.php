<?php

/**
 * To run the tests, you need to set up a database with the content defined in tornelib_tests.sql
 * You should also put the file tornevall_config.json in /etc (or append the current if already exists)
 */

use PHPUnit\Framework\TestCase;
use TorneLIB\MODULE_DATABASE;
use TorneLIB\TORNEVALL_DATABASE_DRIVERS;
use TorneLIB\TORNEVALL_DATABASE_EXCEPTIONS;
use TorneLIB\TORNEVALL_DATABASE_TYPES;

if (file_exists(__DIR__ . "/../vendor/autoload.php")) {
    require_once(__DIR__ . '/../vendor/autoload.php');
} else {
    die("Install composer first");
}

/**
 * Class TorneLIB_DatabaseTest
 */
class databaseTest extends TestCase
{
    /** @var \TorneLIB\MODULE_DATABASE */
    private $DATABASE_INTERFACE;

    /**
     * For this section, check tornelib_tests.sql
     */

    /**
     * @var string $DATABASE_USER_NAME Username for tests
     */
    private $DATABASE_USER_NAME = "tornelib";

    /**
     * @var string $DATABASE_USER_PASSWORD Password for tests
     */
    private $DATABASE_USER_PASSWORD = "tornelib1337";

    /**
     * @var string $DATABASE_SERVER_ADDRESS Connecting to what?
     */
    private $DATABASE_SERVER_ADDRESS = "127.0.0.1";

    /** @var string Server address in ipv6 format. For future tests. */
    private $DATABASE_SERVER_ADDRESS_IVP6 = "::";

    /**
     * @var string $DBName Database with all them tables
     */
    private $DBName = "tornelib_tests";

    public function setUp()
    {
        $this->DATABASE_INTERFACE = new MODULE_DATABASE();
    }

    public function tearDown()
    {
    }

    /**
     * @test
     */
    public function mysqliUserFail()
    {
        try {
            $this->DATABASE_INTERFACE->connect("serverTest", null, null, "nonExistentUserErrcode1045", null);
        } catch (\Exception $e) {
            static::assertEquals($e->getCode(), 1045);
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function mysqliConnect()
    {
        static::assertTrue($connector = $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        ));
    }

    /**
     * @test
     * @throws Exception
     */
    public function mysqlConstruct()
    {
        if (empty($this->DATABASE_USER_PASSWORD)) {
            $this->markTestSkipped("No password set for this test - skipping");

            return;
        }
        /** @var MODULE_DATABASE */
        $SA = null;
        try {
            $SA = new MODULE_DATABASE(
                "testServer",
                [],
                $this->DATABASE_SERVER_ADDRESS,
                $this->DATABASE_USER_NAME,
                $this->DATABASE_USER_PASSWORD,
                TORNEVALL_DATABASE_TYPES::MYSQL,
                $this->DBName
            );
        } catch (\Exception $e) {
        }
        $SA->query_prepare("INSERT INTO tests (`data`) VALUES (?)", [rand(1, 1024)]);
        $iResult = $SA->Query_First("SELECT COUNT(*) c FROM tests");
        static::assertTrue($iResult['c'] > 0);
    }

    /**
     * @test
     */
    public function mysqlPoppableQuery()
    {
        /** @var MODULE_DATABASE */
        $SA = null;
        try {
            $SA = new MODULE_DATABASE(
                "testServer",
                [],
                $this->DATABASE_SERVER_ADDRESS,
                $this->DATABASE_USER_NAME,
                $this->DATABASE_USER_PASSWORD,
                TORNEVALL_DATABASE_TYPES::MYSQL,
                $this->DBName
            );
        } catch (\Exception $e) {
        }
        $SA->query_prepare("INSERT INTO tests (`data`) VALUES (?)", [rand(1, 1024)]);
        $iResult = $SA->Query_First("SELECT COUNT(*) c FROM tests", [], true);
        static::assertTrue($iResult > 0);
    }

    /**
     * @test
     * @throws Exception
     */
    public function mysqlNoConstruct()
    {
        if (empty($this->DATABASE_USER_PASSWORD)) {
            $this->markTestSkipped("No password set for this test - skipping");

            return;
        }
        try {
            $this->DATABASE_INTERFACE->connect(
                null,
                null,
                $this->DATABASE_SERVER_ADDRESS,
                $this->DATABASE_USER_NAME,
                $this->DATABASE_USER_PASSWORD
            );
            $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        } catch (\Exception $e) {
        }
        $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", [rand(1, 1024)]);
        $iResult = $this->DATABASE_INTERFACE->Query_First("SELECT COUNT(*) c FROM tests");
        static::assertTrue($iResult['c'] > 0);
    }

    /**
     * @test
     */
    public function mysqlUserFail()
    {
        try {
            $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_DEPRECATED);
            $this->DATABASE_INTERFACE->connect(null, null, null, "nonExistentUserErrcode1045");
        } catch (\Exception $e) {
            static::assertTrue($e->getCode() == TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_TYPE_MYSQLD_NOT_EXIST || $e->getCode() == 1045);
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function mysqlPConnect()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        static::assertTrue($this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        ));
    }

    /**
     * @test
     */
    public function mysqlPConnectFail()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        try {
            $this->DATABASE_INTERFACE->connect(null, null, null, "nonExistentUserErrcode1045", null);
        } catch (\Exception $e) {
            static::assertTrue($e->getCode() == 1045);
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function changeMysqliDatabase()
    {
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        static::assertTrue($this->DATABASE_INTERFACE->db($this->DBName));
    }

    /**
     * @test
     * @throws Exception
     */
    public function changeMysqliDatabaseOnConnect()
    {
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        static::assertTrue($this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        ));
    }

    /**
     * @test
     */
    public function failChangeMysqliDatabaseOnConnect()
    {
        try {
            $this->DATABASE_INTERFACE->setDatabase("fail");
            static::assertTrue($this->DATABASE_INTERFACE->connect(
                null,
                null,
                $this->DATABASE_SERVER_ADDRESS,
                $this->DATABASE_USER_NAME,
                $this->DATABASE_USER_PASSWORD
            ));
        } catch (\Exception $e) {
            // Using credentials that is not root generates 1044 errors
            static::assertTrue($e->getCode() == 1049 || $e->getCode() == 1044);
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function changeMysqliDatabaseFail()
    {
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        try {
            $this->DATABASE_INTERFACE->db("fail");
        } catch (\Exception $dbError) {
            // Using credentials that is not root generates 1044 errors
            static::assertTrue($dbError->getCode() == 1049 || $dbError->getCode() == 1044);
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function prepareSqliInsertResult()
    {
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        try {
            // Insert to extract
            $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", [rand(1, 1024)]);
            static::assertTrue($this->DATABASE_INTERFACE->query_prepare("SELECT * FROM tests WHERE 1 = ?", [1]));
        } catch (\Exception $e) {
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function prepareSqliInsertResultHardWay()
    {
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        try {
            $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", [rand(1, 1024)]);
            static::assertTrue($this->DATABASE_INTERFACE->query_prepare(
                "SELECT * FROM tests WHERE 1 = ?",
                [1],
                ['META']
            ));
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function prepareSqliGetResult()
    {
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        $this->DATABASE_INTERFACE->db($this->DBName);
        try {
            // Insert to extract
            $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", [rand(1, 1024)]);
            $rows = 0;
            if ($this->DATABASE_INTERFACE->query_prepare(
                "SELECT * FROM tests WHERE 1 = ? ORDER BY data DESC",
                [1]
            )) {
                while ($row = $this->DATABASE_INTERFACE->fetch()) {
                    $rows++;
                    if ($rows >= 10) {
                        break;
                    }
                }
            }
            static::assertTrue($rows > 0);
        } catch (\Exception $e) {
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function prepareSqliGetResultHardWay()
    {
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        try {
            // Insert to extract
            $this->DATABASE_INTERFACE->query_prepare(
                "INSERT INTO tests (`data`) VALUES (?)",
                [rand(1, 1024)],
                ['META']
            );
            $rows = 0;
            if ($this->DATABASE_INTERFACE->query_prepare(
                "SELECT * FROM tests WHERE 1 = ? ORDER BY data DESC",
                [1],
                ['META']
            )) {
                while ($row = $this->DATABASE_INTERFACE->fetch()) {
                    $rows++;
                    if ($rows >= 10) {
                        break;
                    }
                }
            }
            static::assertTrue($rows > 0);
        } catch (\Exception $e) {
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function preparePdoInsert()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        try {
            // Insert.
            static::assertTrue($this->DATABASE_INTERFACE->query_prepare(
                "INSERT INTO " . $this->DBName . ".tests (`data`) VALUES (?)",
                [rand(1, 1024)]
            ));
        } catch (\Exception $e) {
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function sqliQueryFirst()
    {
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", [rand(1, 1024)]);
        $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", [rand(1, 1024)]);
        $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", [rand(1, 1024)]);
        $firstAssoc = $this->DATABASE_INTERFACE->query_first("SELECT * FROM tests WHERE data > ?", [0]);
        static::assertTrue(is_array($firstAssoc) && isset($firstAssoc['data']) && $firstAssoc['data'] >= 0);
    }

    /**
     * @test
     * @throws Exception
     */
    public function sqliQueryFirstEmpty()
    {
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->DATABASE_INTERFACE->query_prepare("TRUNCATE TABLE tests");
        $firstAssoc = $this->DATABASE_INTERFACE->query_first("SELECT * FROM tests WHERE data > ?", [0]);
        static::assertTrue(!is_array($firstAssoc));
    }

    /**
     * @test
     * @throws Exception
     */
    public function sqliRaw()
    {
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->DATABASE_INTERFACE->query_prepare("TRUNCATE TABLE tests");
        $counter = 5;
        while ($counter-- > 0) {
            $this->DATABASE_INTERFACE->query_raw("INSERT INTO tests (`data`) VALUES ('" . rand(1, 1024) . "')");
        }
        $getQuery = $this->DATABASE_INTERFACE->query_raw("SELECT COUNT(*) c FROM tests");
        $fetchFirst = $this->DATABASE_INTERFACE->fetch($getQuery);
        static::assertTrue(isset($fetchFirst['c']) && $fetchFirst['c'] == 5);
    }

    /**
     * @test
     * @throws Exception
     */
    public function pdoQuery()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->DATABASE_INTERFACE->query_prepare("TRUNCATE TABLE tests");
        $counter = 5;
        while ($counter-- > 0) {
            $this->DATABASE_INTERFACE->query_raw("INSERT INTO tests (`data`) VALUES ('" . rand(1, 1024) . "')");
        }
        $getQuery = $this->DATABASE_INTERFACE->query_raw("SELECT COUNT(*) c FROM tests");
        $fetchFirst = $this->DATABASE_INTERFACE->fetch($getQuery);
        static::assertTrue(isset($fetchFirst['c']) && $fetchFirst['c'] == 5);
    }

    /**
     * @test
     * @throws Exception
     */
    public function changePdoDatabase()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        static::assertTrue($this->DATABASE_INTERFACE->db($this->DBName));
    }

    /**
     * @test
     * @throws Exception
     */
    public function escapeSqli()
    {
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        // Very simple test goes here
        $myString = $this->DATABASE_INTERFACE->escape("'");
        static::assertTrue($myString == "\'");
    }

    /**
     * @test
     * @throws Exception
     */
    public function escapePdo()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        $this->DATABASE_INTERFACE->connect(
            null,
            null,
            $this->DATABASE_SERVER_ADDRESS,
            $this->DATABASE_USER_NAME,
            $this->DATABASE_USER_PASSWORD
        );
        $myString = $this->DATABASE_INTERFACE->escape("'");
        static::assertTrue($myString == "\'");
    }
}
