<?php

/**
 * To run the tests, you need to set up a database with the content defined in tornelib_tests.sql
 * You should also put the file tornevall_config.json in /etc (or append the current if already exists)
 */

use TorneLIB\TorneLIB_Database;
use PHPUnit\Framework\TestCase;
use \TorneLIB\TORNEVALL_DATABASE_EXCEPTIONS;
use \TorneLIB\TORNEVALL_DATABASE_DRIVERS;

if (file_exists(__DIR__ . "/../vendor/autoload.php")) {
	require_once( __DIR__ . '/../vendor/autoload.php' );
} else {
	die("Install composer first");
}

/**
 * Class TorneLIB_DatabaseTest
 */
class TorneLIB_DatabaseTest extends TestCase {

	/** @var  TorneLIB_Database */
	private $Database;

	/**
	 * For this section, check tornelib_tests.sql
	 */

	/**
	 * @var string $Username Username for tests
	 */
	private $Username = "tornelib";

	/**
	 * @var string $Password Password for tests
	 */
	private $Password = "tornelib1337";

	/**
	 * @var string $Server Connecting to what?
	 */
	private $Server = "127.0.0.1";

	/**
	 * @var string $DBName Database with all them tables
	 */
	private $DBName = "tornelib_tests";

	public function setUp() {
		$this->Database = new TorneLIB_Database();
	}

	public function tearDown() {
	}

	function testMysqliUserFail() {
		try {
			$this->Database->connect( null, null, null, "nonExistentUserErrcode1045", null );
		} catch ( \Exception $e ) {
			$this->assertTrue( $e->getCode() == 1045 );
		}
	}

	function testMysqliConnect() {
		$this->assertTrue( $this->Database->connect() );
	}

	function testMysqlConstruct() {
		if (empty($this->Password)) {
			$this->markTestSkipped("No password set for this test - skipping");
			return;
		}
		try {
			$SA = new TorneLIB_Database( null, null, $this->Server, $this->Username, $this->Password, \TorneLIB\TORNEVALL_DATABASE_TYPES::MYSQL, $this->DBName );
		} catch ( \Exception $e ) {
		}
		$SA->query_prepare( "INSERT INTO tests (`data`) VALUES (?)", array( rand( 1, 1024 ) ) );
		$iResult = $SA->Query_First( "SELECT COUNT(*) c FROM tests" );
		$this->assertTrue( $iResult['c'] > 0 );
	}

	function testMysqlNoConstruct() {
		if (empty($this->Password)) {
			$this->markTestSkipped("No password set for this test - skipping");
			return;
		}
		try {
			$this->Database->connect( null, null, $this->Server, $this->Username, $this->Password );
			$this->Database->setDatabase( $this->DBName );
		} catch ( \Exception $e ) {
		}
		$this->Database->query_prepare( "INSERT INTO tests (`data`) VALUES (?)", array( rand( 1, 1024 ) ) );
		$iResult = $this->Database->Query_First( "SELECT COUNT(*) c FROM tests" );
		$this->assertTrue( $iResult['c'] > 0 );
	}

	function testMysqlUserFail() {
		try {
			$this->Database->setDriverType( TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_DEPRECATED );
			$this->Database->connect( null, null, null, "nonExistentUserErrcode1045" );
		} catch ( \Exception $e ) {
			$this->assertTrue( $e->getCode() == TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_TYPE_MYSQLD_NOT_EXIST || $e->getCode() == 1045 );
		}
	}

	function testMysqlPConnect() {
		$this->Database->setDriverType( TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO );
		$this->assertTrue( $this->Database->connect() );
	}

	function testMysqlPConnectFail() {
		$this->Database->setDriverType( TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO );
		try {
			$this->Database->connect( null, null, null, "nonExistentUserErrcode1045", null );
		} catch ( \Exception $e ) {
			$this->assertTrue( $e->getCode() == 1045 );
		}
	}

	function testChangeMysqliDatabase() {
		$this->Database->connect();
		$this->assertTrue( $this->Database->db( $this->DBName ) );
	}

	function testChangeMysqliDatabaseOnConnect() {
		$this->Database->setDatabase( $this->DBName );
		$this->assertTrue( $this->Database->connect() );
	}

	function testFailChangeMysqliDatabaseOnConnect() {
		$this->Database->setDatabase( "fail" );
		try {
			$this->assertTrue( $this->Database->connect() );
		} catch ( \Exception $e ) {
			$this->assertTrue( $e->getCode() == 1049 );
		}
	}

	function testChangeMysqliDatabaseFail() {
		$this->Database->connect();
		try {
			$this->Database->db( "fail" );
		} catch ( \Exception $dbError ) {
			$this->assertTrue( $dbError->getCode() == 1049 );
		}
	}

	function testPrepareSqliInsertResult() {
		$this->Database->connect();
		$this->Database->db( $this->DBName );
		try {
			// Insert to extract
			$this->Database->query_prepare( "INSERT INTO tests (`data`) VALUES (?)", array( rand( 1, 1024 ) ) );
			$this->assertTrue( $this->Database->query_prepare( "SELECT * FROM tests WHERE 1 = ?", array( 1 ) ) );
		} catch ( \Exception $e ) {
		}
	}

	function testPrepareSqliInsertResultHardWay() {
		$this->Database->connect();
		$this->Database->db( $this->DBName );
		try {
			$this->Database->query_prepare( "INSERT INTO tests (`data`) VALUES (?)", array( rand( 1, 1024 ) ) );
			$this->assertTrue( $this->Database->query_prepare( "SELECT * FROM tests WHERE 1 = ?", array( 1 ), array( 'META' ) ) );
		} catch ( \Exception $e ) {
			echo $e->getMessage();
		}
	}

	function testPrepareSqliGetResult() {
		$this->Database->connect();
		$this->Database->db( $this->DBName );
		try {
			// Insert to extract
			$this->Database->query_prepare( "INSERT INTO tests (`data`) VALUES (?)", array( rand( 1, 1024 ) ) );
			$rows = 0;
			if ( $this->Database->query_prepare( "SELECT * FROM tests WHERE 1 = ? ORDER BY data DESC", array( 1 ) ) ) {
				while ( $row = $this->Database->fetch() ) {
					$rows ++;
					if ( $rows >= 10 ) {
						break;
					}
				}
			}
			$this->assertTrue( $rows > 0 );
		} catch ( \Exception $e ) {
		}
	}

	function testPrepareSqliGetResultHardWay() {
		$this->Database->connect();
		$this->Database->db( $this->DBName );
		try {
			// Insert to extract
			$this->Database->query_prepare( "INSERT INTO tests (`data`) VALUES (?)", array( rand( 1, 1024 ) ), array( 'META' ) );
			$rows = 0;
			if ( $this->Database->query_prepare( "SELECT * FROM tests WHERE 1 = ? ORDER BY data DESC", array( 1 ), array( 'META' ) ) ) {
				while ( $row = $this->Database->fetch() ) {
					$rows ++;
					if ( $rows >= 10 ) {
						break;
					}
				}
			}
			$this->assertTrue( $rows > 0 );
		} catch ( \Exception $e ) {
		}
	}

	function testPreparePdoInsert() {
		$this->Database->setDriverType( TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO );
		$this->Database->connect();
		$this->Database->db( $this->DBName );
		try {
			// Insert.
			$this->assertTrue( $this->Database->query_prepare( "INSERT INTO " . $this->DBName . ".tests (`data`) VALUES (?)", array( rand( 1, 1024 ) ) ) );
		} catch ( \Exception $e ) {
		}
	}

	function testSqliQueryFirst() {
		$this->Database->connect();
		$this->Database->db( $this->DBName );
		$this->Database->query_prepare( "INSERT INTO tests (`data`) VALUES (?)", array( rand( 1, 1024 ) ) );
		$this->Database->query_prepare( "INSERT INTO tests (`data`) VALUES (?)", array( rand( 1, 1024 ) ) );
		$this->Database->query_prepare( "INSERT INTO tests (`data`) VALUES (?)", array( rand( 1, 1024 ) ) );
		$firstAssoc = $this->Database->query_first( "SELECT * FROM tests WHERE data > ?", array( 0 ) );
		$this->assertTrue( is_array( $firstAssoc ) && isset( $firstAssoc['data'] ) && $firstAssoc['data'] >= 0 );
	}

	function testSqliQueryFirstEmpty() {
		$this->Database->connect();
		$this->Database->db( $this->DBName );
		$this->Database->query_prepare( "TRUNCATE TABLE tests" );
		$firstAssoc = $this->Database->query_first( "SELECT * FROM tests WHERE data > ?", array( 0 ) );
		$this->assertTrue( ! is_array( $firstAssoc ) );
	}

	function testSqliRaw() {
		$this->Database->connect();
		$this->Database->db( $this->DBName );
		$this->Database->query_prepare( "TRUNCATE TABLE tests" );
		$counter = 5;
		while ( $counter -- > 0 ) {
			$this->Database->query_raw( "INSERT INTO tests (`data`) VALUES ('" . rand( 1, 1024 ) . "')" );
		}
		$getQuery   = $this->Database->query_raw( "SELECT COUNT(*) c FROM tests" );
		$fetchFirst = $this->Database->fetch( $getQuery );
		$this->assertTrue( isset( $fetchFirst['c'] ) && $fetchFirst['c'] == 5 );
	}

	function testPdoQuery() {
		$this->Database->setDriverType( TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO );
		$this->Database->connect();
		$this->Database->db( $this->DBName );
		$this->Database->query_prepare( "TRUNCATE TABLE tests" );
		$counter = 5;
		while ( $counter -- > 0 ) {
			$this->Database->query_raw( "INSERT INTO tests (`data`) VALUES ('" . rand( 1, 1024 ) . "')" );
		}
		$getQuery   = $this->Database->query_raw( "SELECT COUNT(*) c FROM tests" );
		$fetchFirst = $this->Database->fetch( $getQuery );
		$this->assertTrue( isset( $fetchFirst['c'] ) && $fetchFirst['c'] == 5 );
	}

	function testChangePdoDatabase() {
		$this->Database->setDriverType( TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO );
		$this->Database->connect();
		$this->assertTrue( $this->Database->db( $this->DBName ) );
	}

	function testEscapeSqli() {
		$this->Database->connect();
		$this->Database->db( $this->DBName );
		// Very simple test goes here
		$myString = $this->Database->escape( "'" );
		$this->assertTrue( $myString == "\'" );

	}

	function testEscapePdo() {
		$this->Database->setDriverType( TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO );
		$this->Database->connect();
		$myString = $this->Database->escape( "'" );
		$this->assertTrue( $myString == "\'" );
	}

}
