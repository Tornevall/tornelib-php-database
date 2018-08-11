<?php

/**
 * To run the tests, you need to set up a database with the content defined in tornelib_tests.sql
 * You should also put the file tornevall_config.json in /etc (or append the current if already exists)
 */

use TorneLIB\TorneLIB_Database;
use PHPUnit\Framework\TestCase;
use \TorneLIB\TORNEVALL_DATABASE_EXCEPTIONS;
use \TorneLIB\TORNEVALL_DATABASE_DRIVERS;
use \TorneLIB\TORNEVALL_DATABASE_TYPES;
use \TorneLIB\MODULE_DATABASE;

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
    function mysqliUserFail()
    {
        try {
            $this->DATABASE_INTERFACE->connect("serverTest", null, null, "nonExistentUserErrcode1045", null);
        } catch (\Exception $e) {
            $this->assertTrue($e->getCode() == 1045);
        }
    }

    /**
     * @test
     * @throws Exception
     */
    function mysqliConnect()
    {
        $this->assertTrue($this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD));
    }

    /**
     * @test
     * @throws Exception
     */
    function mysqlConstruct()
    {
        if (empty($this->DATABASE_USER_PASSWORD)) {
            $this->markTestSkipped("No password set for this test - skipping");
            return;
        }
        /** @var MODULE_DATABASE */
        $SA = null;
        try {
            $SA = new MODULE_DATABASE("testServer", array(), $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD,
                TORNEVALL_DATABASE_TYPES::MYSQL, $this->DBName);
        } catch (\Exception $e) {
        }
        $SA->query_prepare("INSERT INTO tests (`data`) VALUES (?)", array(rand(1, 1024)));
        $iResult = $SA->Query_First("SELECT COUNT(*) c FROM tests");
        $this->assertTrue($iResult['c'] > 0);
    }

    /**
     * @test
     */
    function mysqlPoppableQuery() {
        /** @var MODULE_DATABASE */
        $SA = null;
        try {
            $SA = new MODULE_DATABASE("testServer", array(), $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD,
                TORNEVALL_DATABASE_TYPES::MYSQL, $this->DBName);
        } catch (\Exception $e) {
        }
        $SA->query_prepare("INSERT INTO tests (`data`) VALUES (?)", array(rand(1, 1024)));
        $iResult = $SA->Query_First("SELECT COUNT(*) c FROM tests", array(), true);
        $this->assertTrue($iResult > 0);
    }

    /**
     * @test
     * @throws Exception
     */
    function mysqlNoConstruct()
    {
        if (empty($this->DATABASE_USER_PASSWORD)) {
            $this->markTestSkipped("No password set for this test - skipping");
            return;
        }
        try {
            $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
            $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        } catch (\Exception $e) {
        }
        $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", array(rand(1, 1024)));
        $iResult = $this->DATABASE_INTERFACE->Query_First("SELECT COUNT(*) c FROM tests");
        $this->assertTrue($iResult['c'] > 0);
    }

    /**
     * @test
     */
    function mysqlUserFail()
    {
        try {
            $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_DEPRECATED);
            $this->DATABASE_INTERFACE->connect(null, null, null, "nonExistentUserErrcode1045");
        } catch (\Exception $e) {
            $this->assertTrue($e->getCode() == TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_TYPE_MYSQLD_NOT_EXIST || $e->getCode() == 1045);
        }
    }

    /**
     * @test
     * @throws Exception
     */
    function mysqlPConnect()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        $this->assertTrue($this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD));
    }

    /**
     * @test
     */
    function mysqlPConnectFail()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        try {
            $this->DATABASE_INTERFACE->connect(null, null, null, "nonExistentUserErrcode1045", null);
        } catch (\Exception $e) {
            $this->assertTrue($e->getCode() == 1045);
        }
    }

    /**
     * @test
     * @throws Exception
     */
    function changeMysqliDatabase()
    {
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->assertTrue($this->DATABASE_INTERFACE->db($this->DBName));
    }

    /**
     * @test
     * @throws Exception
     */
    function changeMysqliDatabaseOnConnect()
    {
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->assertTrue($this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD));
    }

    /**
     * @test
     */
    function failChangeMysqliDatabaseOnConnect()
    {
        try {
            $this->DATABASE_INTERFACE->setDatabase("fail");
            $this->assertTrue($this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD));
        } catch (\Exception $e) {
            // Using credentials that is not root generates 1044 errors
            $this->assertTrue($e->getCode() == 1049 || $e->getCode() == 1044);
        }
    }

    /**
     * @test
     * @throws Exception
     */
    function changeMysqliDatabaseFail()
    {
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        try {
            $this->DATABASE_INTERFACE->db("fail");
        } catch (\Exception $dbError) {
            // Using credentials that is not root generates 1044 errors
            $this->assertTrue($dbError->getCode() == 1049 || $dbError->getCode() == 1044);
        }
    }

    /**
     * @test
     * @throws Exception
     */
    function prepareSqliInsertResult()
    {
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        try {
            // Insert to extract
            $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", array(rand(1, 1024)));
            $this->assertTrue($this->DATABASE_INTERFACE->query_prepare("SELECT * FROM tests WHERE 1 = ?", array(1)));
        } catch (\Exception $e) {
        }
    }

    /**
     * @test
     * @throws Exception
     */
    function prepareSqliInsertResultHardWay()
    {
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        try {
            $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", array(rand(1, 1024)));
            $this->assertTrue($this->DATABASE_INTERFACE->query_prepare("SELECT * FROM tests WHERE 1 = ?", array(1),
                array('META')));
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @test
     * @throws Exception
     */
    function prepareSqliGetResult()
    {
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->DATABASE_INTERFACE->db($this->DBName);
        try {
            // Insert to extract
            $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", array(rand(1, 1024)));
            $rows = 0;
            if ($this->DATABASE_INTERFACE->query_prepare("SELECT * FROM tests WHERE 1 = ? ORDER BY data DESC", array(1))) {
                while ($row = $this->DATABASE_INTERFACE->fetch()) {
                    $rows++;
                    if ($rows >= 10) {
                        break;
                    }
                }
            }
            $this->assertTrue($rows > 0);
        } catch (\Exception $e) {
        }
    }

    /**
     * @test
     * @throws Exception
     */
    function prepareSqliGetResultHardWay()
    {
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        try {
            // Insert to extract
            $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", array(rand(1, 1024)),
                array('META'));
            $rows = 0;
            if ($this->DATABASE_INTERFACE->query_prepare("SELECT * FROM tests WHERE 1 = ? ORDER BY data DESC", array(1),
                array('META'))) {
                while ($row = $this->DATABASE_INTERFACE->fetch()) {
                    $rows++;
                    if ($rows >= 10) {
                        break;
                    }
                }
            }
            $this->assertTrue($rows > 0);
        } catch (\Exception $e) {
        }
    }

    /**
     * @test
     * @throws Exception
     */
    function preparePdoInsert()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        try {
            // Insert.
            $this->assertTrue($this->DATABASE_INTERFACE->query_prepare("INSERT INTO " . $this->DBName . ".tests (`data`) VALUES (?)",
                array(rand(1, 1024))));
        } catch (\Exception $e) {
        }
    }

    /**
     * @test
     * @throws Exception
     */
    function sqliQueryFirst()
    {
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", array(rand(1, 1024)));
        $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", array(rand(1, 1024)));
        $this->DATABASE_INTERFACE->query_prepare("INSERT INTO tests (`data`) VALUES (?)", array(rand(1, 1024)));
        $firstAssoc = $this->DATABASE_INTERFACE->query_first("SELECT * FROM tests WHERE data > ?", array(0));
        $this->assertTrue(is_array($firstAssoc) && isset($firstAssoc['data']) && $firstAssoc['data'] >= 0);
    }

    /**
     * @test
     * @throws Exception
     */
    function sqliQueryFirstEmpty()
    {
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->DATABASE_INTERFACE->query_prepare("TRUNCATE TABLE tests");
        $firstAssoc = $this->DATABASE_INTERFACE->query_first("SELECT * FROM tests WHERE data > ?", array(0));
        $this->assertTrue(!is_array($firstAssoc));
    }

    /**
     * @test
     * @throws Exception
     */
    function sqliRaw()
    {
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->DATABASE_INTERFACE->query_prepare("TRUNCATE TABLE tests");
        $counter = 5;
        while ($counter-- > 0) {
            $this->DATABASE_INTERFACE->query_raw("INSERT INTO tests (`data`) VALUES ('" . rand(1, 1024) . "')");
        }
        $getQuery = $this->DATABASE_INTERFACE->query_raw("SELECT COUNT(*) c FROM tests");
        $fetchFirst = $this->DATABASE_INTERFACE->fetch($getQuery);
        $this->assertTrue(isset($fetchFirst['c']) && $fetchFirst['c'] == 5);
    }

    /**
     * @test
     * @throws Exception
     */
    function pdoQuery()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        $this->DATABASE_INTERFACE->query_prepare("TRUNCATE TABLE tests");
        $counter = 5;
        while ($counter-- > 0) {
            $this->DATABASE_INTERFACE->query_raw("INSERT INTO tests (`data`) VALUES ('" . rand(1, 1024) . "')");
        }
        $getQuery = $this->DATABASE_INTERFACE->query_raw("SELECT COUNT(*) c FROM tests");
        $fetchFirst = $this->DATABASE_INTERFACE->fetch($getQuery);
        $this->assertTrue(isset($fetchFirst['c']) && $fetchFirst['c'] == 5);
    }

    /**
     * @test
     * @throws Exception
     */
    function changePdoDatabase()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->assertTrue($this->DATABASE_INTERFACE->db($this->DBName));
    }

    /**
     * @test
     * @throws Exception
     */
    function escapeSqli()
    {
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $this->DATABASE_INTERFACE->setDatabase($this->DBName);
        // Very simple test goes here
        $myString = $this->DATABASE_INTERFACE->escape("'");
        $this->assertTrue($myString == "\'");

    }

    /**
     * @test
     * @throws Exception
     */
    function escapePdo()
    {
        $this->DATABASE_INTERFACE->setDriverType(TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO);
        $this->DATABASE_INTERFACE->connect(null, null, $this->DATABASE_SERVER_ADDRESS, $this->DATABASE_USER_NAME, $this->DATABASE_USER_PASSWORD);
        $myString = $this->DATABASE_INTERFACE->escape("'");
        $this->assertTrue($myString == "\'");
    }

}
