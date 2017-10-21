<?php

namespace TorneLIB;

require_once __DIR__ . "/tornevall_database_abstracts.php";
require_once __DIR__ . "/tornevall_database_interface.php";
require_once __DIR__ . "/tornevall_database_driver_mysql.php";

/**
 * Class TorneLIB_Database
 * @package TorneLIB
 * @version 6.0.0
 */
class TorneLIB_Database {

	/** @var Identifier name */
	private $serverIdentifier;
	/** @var Options in */
	private $serverOptions;
	/** @var Hostname or address */
	private $serverHostAddr;
	/** @var Server username */
	private $serverUsername;
	/** @var Server password */
	private $serverPassword;
	/** @var int Server port name, defaults to mysql */
	private $serverPort = 3306;
	/** @var Predefined datbase name to connect to */
	private $serverDatabaseName;

	/** @var TORNEVALL_DATABASE_TYPES */
	private $serverType;
	/** @var TORNEVALL_DATABASE_DRIVERS */
	private $serverDriverType;

	/** @var libdriver_interface */
	private $serverResource;
	/** @var bool Specifies if the database initializer should recreate resource for each new instance - normally we'd like to keep the old resource so it will be possible to enforce drivers */
	private $forceNewResource = false;

	/**
	 * TorneLIB_Database constructor.
	 *
	 * @param string $serverIdentifier
	 * @param array $serverOptions
	 * @param null $serverHostAddr
	 * @param null $serverUsername
	 * @param null $serverPassword
	 * @param int $serverType
	 * @param string $databaseName
	 */
	function __construct( $serverIdentifier = '', $serverOptions = array(), $serverHostAddr = null, $serverUsername = null, $serverPassword = null, $serverType = TORNEVALL_DATABASE_TYPES::MYSQL, $databaseName = '' ) {
		$this->setServerIdentifier( $serverIdentifier );
		$this->setServerOptions( $serverOptions );
		$this->setServerHostAddr( $serverHostAddr );
		$this->setServerUserName( $serverUsername );
		$this->setServerPassword( $serverPassword );
		$this->setServerType( $serverType );
		$this->serverType = $serverType;
		if ( ! empty( $databaseName ) ) {
			$this->setDatabase( $databaseName );
		}
	}

	/**
	 * Initialize database
	 *
	 * This method normally initializes a database resource once and no more.
	 *
	 * @param bool $newDriver
	 *
	 * @return bool
	 */
	private function initializeDatabaseDriver( $newDriver = false ) {
		if ( ( is_object( $this->serverResource ) || is_resource( $this->serverResource ) ) ) {
			if ( ! $newDriver ) {
				$this->forceNewResource = $newDriver;

				return true;
			}
		}
		if ( $this->serverType == TORNEVALL_DATABASE_TYPES::MYSQL ) {
			$this->serverResource = new libdriver_mysql( $this->getServerIdentifier(), $this->getServerOptions(), $this->getServerHostAddr(), $this->getServerUserName(), $this->getServerPassword() );
			$this->serverResource->setPort( $this->getPort() );
			$this->serverResource->setDatabase( $this->getDatabase() );
			if ( $this->serverDriverType != TORNEVALL_DATABASE_DRIVERS::DRIVER_TYPE_NONE && method_exists( $this->serverResource, "setDriverType" ) ) {
				$this->setDriverType( $this->serverDriverType );
			}
		}
	}

	/**
	 * Identify current server with name
	 *
	 * @param string $serverIdentifier
	 */
	public function setServerIdentifier( $serverIdentifier = '' ) {
		$this->serverIdentifier = ! empty( $serverIdentifier ) ? $serverIdentifier : "default";
	}

	/**
	 * Get server name (identification)
	 * @return Identifier
	 */
	public function getServerIdentifier() {
		return $this->serverIdentifier;
	}

	/**
	 * Set special options for database
	 *
	 * @param array $serverOptions
	 */
	public function setServerOptions( $serverOptions = array() ) {
		if ( is_array( $serverOptions ) && count( $serverOptions ) ) {
			$this->serverOptions = $serverOptions;
		}
	}

	/**
	 * Get currrent set server options
	 * @return Options
	 */
	public function getServerOptions() {
		return $this->serverOptions;
	}

	/**
	 * Set up host/addr to database server
	 *
	 * @param string $serverHostAddr
	 */
	public function setServerHostAddr( $serverHostAddr = '' ) {
		if ( ! empty( $serverHostAddr ) ) {
			$this->serverHostAddr = $serverHostAddr;
		}
	}

	/**
	 * Get current set host/addr to database server
	 * @return Hostname
	 */
	public function getServerHostAddr() {
		return $this->serverHostAddr;
	}

	/**
	 * Set username credentials
	 *
	 * @param string $serverUsername
	 */
	public function setServerUserName( $serverUsername = '' ) {
		if ( ! empty( $serverUsername ) ) {
			$this->serverUsername = $serverUsername;
		}
	}

	/**
	 * Get current username credentials
	 * @return Server
	 */
	public function getServerUserName() {
		return $this->serverUsername;
	}

	/**
	 * Set current password credentials
	 *
	 * @param string $serverPassword
	 */
	public function setServerPassword( $serverPassword = '' ) {
		if ( ! empty( $serverPassword ) ) {
			$this->serverPassword = $serverPassword;
		}
	}

	/**
	 * Get current password credentials
	 * @return Server
	 */
	public function getServerPassword() {
		return $this->serverPassword;
	}

	/**
	 * Change default connector port
	 *
	 * @param int $serverPortNumber
	 */
	public function setPort( $serverPortNumber = 3306 ) {
		if ( ! empty( $serverPortNumber ) && is_numeric( $serverPortNumber ) ) {
			$this->serverPort = $serverPortNumber;
		}
	}

	/**
	 * Get the default connector port
	 *
	 * @return int
	 */
	public function getPort() {
		return $this->serverPort;
	}

	/**
	 * Preconfigure database to connect to
	 *
	 * @param string $databaseName
	 */
	public function setDatabase( $databaseName = '' ) {
		if ( ! empty( $databaseName ) ) {
			$this->serverDatabaseName = $databaseName;
		}
	}

	/**
	 * Get preconfigured database to connect to
	 *
	 * @return mixed
	 */
	public function getDatabase() {
		return $this->serverDatabaseName;
	}
	//// INTERFACE SETUP END

	//// BASIC FUNCTIONS

	/**
	 * @param $ownServerDriver libdriver_interface
	 */
	public function setServerDriver( $ownServerDriver ) {
		$this->serverResource = $ownServerDriver;
	}

	/**
	 * Set up which driver that should be used on connection
	 *
	 * @param int $serverType
	 */
	public function setServerType( $serverType = TORNEVALL_DATABASE_TYPES::MYSQL ) {
		$this->serverType = $serverType;
	}

	/**
	 * @return int|TORNEVALL_DATABASE_TYPES
	 */
	public function getServerType() {
		return $this->serverType;
	}

	/**
	 * @param string $serverIdentifier
	 * @param array $serverOptions
	 * @param null $serverHostAddr
	 * @param null $serverUsername
	 * @param null $serverPassword
	 * @param bool $forceNew
	 *
	 * @return mixed
	 */
	public function connect( $serverIdentifier = '', $serverOptions = array(), $serverHostAddr = null, $serverUsername = null, $serverPassword = null, $forceNew = false ) {
		$this->setServerIdentifier( $serverIdentifier );
		$this->setServerOptions( $serverOptions );
		$this->setServerHostAddr( $serverHostAddr );
		$this->setServerUserName( $serverUsername );
		$this->setServerPassword( $serverPassword );
		$this->initializeDatabaseDriver( $forceNew );

		return $this->serverResource->connect( $this->getServerIdentifier(), $this->getServerOptions(), $this->getServerHostAddr(), $this->getServerUserName(), $this->getServerPassword() );
	}

	/**
	 * Let each driver handle own communication
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed|null
	 */
	public function __call( $name, $arguments ) {
		$returnedCall = null;
		if ( empty( $this->serverResource ) ) {
			$this->initializeDatabaseDriver( $this->forceNewResource );
		}
		$returnedCall = @call_user_func_array( array( $this->serverResource, $name ), $arguments );

		return $returnedCall;
	}

}