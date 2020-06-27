<?php

/**
 * Copyright 2017 Tomas Tornevall & Tornevall Networks
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package TorneLIB
 * @version 6.0.6
 *
 */

namespace TorneLIB;

use Exception;

require_once __DIR__ . "/tornevall_database_interface.php";

if (!class_exists('libdriver_mysql') && !class_exists('TorneLIB\libdriver_mysql')) {

    /**
     * Class libdriver_mysql
     * @package TorneLIB
     */
    class libdriver_mysql implements libdriver_database_interface
    {
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

        private $TIMEOUT = 10;
        private $mysqlPreparedResult;
        /** @var int Affected rows during query */
        private $mysqlAffectedRows;
        /** @var int Last insert id */
        private $lastInsertId;

        /** @var The resource */
        private $dataResource;
        /** @var int Database driver type */
        private $preferredDriverType = TORNEVALL_DATABASE_DRIVERS::DRIVER_TYPE_NONE;
        /** @var bool User enforced driver */
        private $preferredDriverTypeEnforced;
        /** @var object JSON configuration */
        private $CONFIG;
        /** @var bool Set up to return object instead of array for PDO */
        private $getAsSqlObject;

        /**
         * libdriver_mysql constructor.
         *
         * @param string $serverIdentifier
         * @param array $serverOptions
         * @param null $serverHostAddr
         * @param null $serverUsername
         * @param null $serverPassword
         *
         * @throws \Exception
         */
        public function __construct(
            $serverIdentifier = '',
            $serverOptions = [],
            $serverHostAddr = null,
            $serverUsername = null,
            $serverPassword = null
        ) {
            if (is_null($serverOptions)) {
                $serverOptions = [];
            }
            $this->setServerIdentifier($serverIdentifier);
            $this->setServerOptions($serverOptions);
            $this->setServerHostAddr($serverHostAddr);
            $this->setServerUserName($serverUsername);
            $this->setServerPassword($serverPassword);
            $this->setConfig();
            if (!is_null($serverHostAddr)) {
                $this->getValidDrivers();
                $this->connect(
                    $this->getServerIdentifier(),
                    $this->getServerOptions(),
                    $this->getServerHostAddr(),
                    $this->getServerUserName(),
                    $this->getServerPassword()
                );
            }
        }

        /**
         * Shut down driver
         */
        public function __destruct()
        {
            if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_IMPROVED) {
                if (!empty($this->mysqlPreparedResult)) {
                    try {
                        // PHP 5.4 issue
                        @mysqli_free_result($this->mysqlPreparedResult);
                    } catch (\Exception $e) {
                        // Only free results if it is possible
                    }
                }
            } else {
                if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_DEPRECATED) {
                    try {
                        if (!empty($this->dataResource)) {
                            @mysql_free_result($this->dataResource);
                        }
                    } catch (\Exception $freeResultException) {
                        // This one did not want to free anything
                    }
                }
            }
        }

        /**
         * @param string $configJsonFile
         *
         * @throws \Exception
         */
        public function setConfig($configJsonFile = '/etc/tornevall_config.json')
        {
            if (file_exists($configJsonFile)) {
                $jsonConfig = @file_get_contents($configJsonFile);
                if (!empty($jsonConfig)) {
                    $jsonConfigParsed = @json_decode($jsonConfig);
                    if (isset($jsonConfigParsed->database->servers)) {
                        $this->CONFIG = $jsonConfigParsed->database->servers;
                    }
                }
            }
            if ($configJsonFile != "/etc/tornevall_config.json" && !file_exists($configJsonFile)) {
                throw new \Exception(
                    "File $configJsonFile is missing",
                    TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_CONFIGURATION_MISSING
                );
            }
        }

        /**
         * @throws \Exception
         */
        private function getDataFromConfig()
        {
            $useDataSource = "";

            $checkAddr = $this->getServerHostAddr();    // PHP 5.3-compatible control
            $checkUser = $this->getServerUserName();

            // Check if there are user data
            if (empty($checkAddr) && empty($checkUser)) {
                if (isset($this->CONFIG)) {
                    if (isset($this->CONFIG->localhost)) {
                        $useDataSource = "localhost";
                    } else {
                        if (isset($this->CONFIG->default)) {
                            $useDataSource = "default";
                        }
                    }
                }

                if (!empty($useDataSource)) {
                    $dataSource = $this->CONFIG->$useDataSource;
                    if (isset($dataSource->user) && isset($dataSource->server) && isset($dataSource->password)) {
                        if (empty($this->serverHostAddr)) {
                            $this->setServerHostAddr($dataSource->server);
                        }
                        if (empty($this->serverUsername)) {
                            $this->setServerUserName($dataSource->user);
                        }
                        if (empty($this->serverPassword)) {
                            $this->setServerPassword($dataSource->password);
                        }
                        if (isset($dataSource->db)) {
                            $this->setDatabase($dataSource->db);
                        }
                    } else {
                        throw new \Exception("No user credentials has been set up to use with MySQL driver", 500);
                    }
                } else {
                    throw new \Exception("No user credentials has been set up to use with MySQL driver", 500);
                }
            }
        }

        /*****
         * INTERFACE SETUP BEGIN
         */

        /**
         * Identify current server with name
         *
         * @param string $serverIdentifier
         */
        public function setServerIdentifier($serverIdentifier = '')
        {
            $this->serverIdentifier = !empty($serverIdentifier) ? $serverIdentifier : "default";
        }

        /**
         * Get server name (identification)
         * @return Identifier
         */
        public function getServerIdentifier()
        {
            return $this->serverIdentifier;
        }

        /**
         * Set special options for database
         *
         * @param array $serverOptions
         */
        public function setServerOptions($serverOptions = [])
        {
            if (is_array($serverOptions) && count($serverOptions)) {
                $this->serverOptions = $serverOptions;
            }
        }

        /**
         * Get currrent set server options
         * @return Options
         */
        public function getServerOptions()
        {
            return $this->serverOptions;
        }

        /**
         * Set up host/addr to database server
         *
         * @param string $serverHostAddr
         */
        public function setServerHostAddr($serverHostAddr = '')
        {
            if (!empty($serverHostAddr)) {
                $this->serverHostAddr = $serverHostAddr;
            }
        }

        /**
         * Get current set host/addr to database server
         * @return Hostname
         */
        public function getServerHostAddr()
        {
            return $this->serverHostAddr;
        }

        /**
         * Set username credentials
         *
         * @param string $serverUsername
         */
        public function setServerUserName($serverUsername = '')
        {
            if (!empty($serverUsername)) {
                $this->serverUsername = $serverUsername;
            }
        }

        /**
         * Get current username credentials
         * @return Server
         */
        public function getServerUserName()
        {
            return $this->serverUsername;
        }

        /**
         * Set current password credentials
         *
         * @param string $serverPassword
         */
        public function setServerPassword($serverPassword = '')
        {
            if (!empty($serverPassword)) {
                $this->serverPassword = $serverPassword;
            }
        }

        /**
         * Get current password credentials
         * @return Server
         */
        public function getServerPassword()
        {
            return $this->serverPassword;
        }

        /**
         * Change default connector port
         *
         * @param int $serverPortNumber
         */
        public function setPort($serverPortNumber = 3306)
        {
            if (!empty($serverPortNumber) && is_numeric($serverPortNumber)) {
                $this->serverPort = $serverPortNumber;
            }
        }

        /**
         * Get the default connector port
         *
         * @return int
         */
        public function getPort()
        {
            return $this->serverPort;
        }

        /**
         * @param $ownServerDriver libdriver_database_interface
         */
        public function setServerDriver($ownServerDriver)
        {
            $this->serverDriver = $ownServerDriver;
        }

        /**
         * Preconfigure database to connect to
         *
         * @param string $databaseName
         *
         * @throws \Exception
         */
        public function setDatabase($databaseName = '')
        {
            if (!empty($databaseName)) {
                $this->serverDatabaseName = $databaseName;
            }
            if (!empty($this->dataResource)) {
                $this->db($this->serverDatabaseName);
            }
        }

        /**
         * Get preconfigured database to connect to
         *
         * @return mixed
         */
        public function getDatabase()
        {
            return $this->serverDatabaseName;
        }

        /*
         * INTERFACE SETUP END
         *****/

        /*****
         * INTERFACE FUNCTIONS DEPENDENT ON DRIVER
         */

        /**
         * Configure this module for a "preferred mysql driver"
         */
        private function prepareConnect()
        {
            // Some say that PDO is better than the other drivers. Some say they are not.
            // However, the "improved driver" has a good sql injection protection that we are using - for now.

            $this->getDataFromConfig();

            if ($this->preferredDriverType == TORNEVALL_DATABASE_DRIVERS::DRIVER_TYPE_NONE && $this->getDriverType() !== TORNEVALL_DATABASE_DRIVERS::DRIVER_TYPE_NONE) {
                $this->preferredDriverType = $this->getDriverType();

                return $this->preferredDriverType;
            }

            $this->getValidDrivers();

            return $this->preferredDriverType;
        }

        /**
         * Make sure there is a valid database driver on connect (especially if running from constructor level)
         */
        private function getValidDrivers()
        {
            if (function_exists('mysqli_connect')) {
                $this->preferredDriverType = TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_IMPROVED;
            } else {
                if (function_exists('mysql_connect')) {
                    $this->preferredDriverType = TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_DEPRECATED;
                } else {
                    // Fall back to PDO
                    if (class_exists('PDO')) {
                        $pdoDriversStatic = \PDO::getAvailableDrivers();
                        if (in_array('mysql', $pdoDriversStatic)) {
                            $this->preferredDriverType = TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO;
                        }
                    }
                }
            }
        }

        /**
         * If there are several drivers to choose from (PDO, ODBC, etc) use this setter.
         *
         * @param int $serverDriverType
         */
        public function setDriverType($serverDriverType = TORNEVALL_DATABASE_DRIVERS::DRIVER_TYPE_NONE)
        {
            $this->preferredDriverTypeEnforced = $serverDriverType;
        }

        /**
         * Return information about chosen subdriver (if any)
         *
         * @return bool|int
         */
        public function getDriverType()
        {
            if (!empty($this->preferredDriverTypeEnforced)) {
                return $this->preferredDriverTypeEnforced;
            }

            return $this->preferredDriverType;
        }

        /**
         * Try to set options for a resource
         *
         * @param $currentResource
         */
        private function setSqlOptions($currentResource)
        {
            if (is_object($currentResource) || is_resource($currentResource)) {
                $opt = $this->getServerOptions();
                if (!isset($opt[MYSQLI_OPT_CONNECT_TIMEOUT])) {
                    $opt[MYSQLI_OPT_CONNECT_TIMEOUT] = $this->TIMEOUT;
                }
                foreach ($opt as $key => $val) {
                    if ($this->preferredDriverType == TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_IMPROVED) {
                        mysqli_options($currentResource, $key, $val);
                    }
                }
            }
        }

        /**
         * Connect do mysql with mysqli driver
         * @return bool
         * @throws \Exception
         */
        private function CONNECT_MYSQL_IMPROVED()
        {
            $connectSuccess = false;
            if (function_exists('mysqli_connect')) {
                $connectResource = @mysqli_connect(
                    $this->getServerHostAddr(),
                    $this->getServerUserName(),
                    $this->getServerPassword(),
                    $this->getDatabase(),
                    $this->getPort()
                );
                $this->setSqlOptions($connectResource);
                if (mysqli_connect_errno()) {
                    throw new \Exception(__FUNCTION__ . ": " . mysqli_connect_error(), mysqli_connect_errno());
                } else {
                    if (mysqli_errno($connectResource)) {
                        throw new \Exception(
                            __FUNCTION__ . ": " . mysqli_error($connectResource),
                            mysqli_errno($connectResource)
                        );
                    } else {
                        if (is_object($connectResource)) {
                            $this->dataResource = $connectResource;
                            $this->db();
                            $connectSuccess = true;
                        }
                    }
                }
            } else {
                throw new \Exception(
                    __FUNCTION__ . ": You are trying to use a database driver that does not exist (mysqli_connect)",
                    TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_TYPE_MYSQLI_NOT_EXIST
                );
            }

            return $connectSuccess;
        }

        /**
         * Connect to mysql with PDO driver
         * @return bool
         * @throws \Exception
         */
        private function CONNECT_PDO()
        {
            $connectSuccess = false;
            if (class_exists('PDO')) {
                $pdoDriversStatic = \PDO::getAvailableDrivers();
                if (!in_array('mysql', $pdoDriversStatic)) {
                    throw new \Exception(
                        "You are trying to use a database driver that does not exist (PDO)",
                        TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_TYPE_MYSQLP_NOT_EXIST
                    );
                }
                $DSN = 'mysql:dbname=' . $this->getDatabase() . ';host=' . $this->getServerHostAddr();
                $this->PDO = new \PDO(
                    $DSN,
                    $this->getServerUserName(),
                    $this->getServerPassword(),
                    $this->getServerOptions()
                );
                if (is_object($this->PDO)) {
                    /** @var dataResource \PDO */
                    $this->dataResource = $this->PDO;
                    $this->dataResource->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    $this->db();
                    $connectSuccess = true;
                } else {
                    throw new \Exception(
                        "Could not connect to PDO",
                        TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_TYPE_MYSQLP_CONNECT_UNKNOWN_ERROR
                    );
                }
            }

            return $connectSuccess;
        }

        /**
         * Successful connects returns true
         * @return bool
         * @throws \Exception
         */
        private function CONNECT_MYSQL_DEPRECATED()
        {
            $connectSuccess = false;
            if (function_exists('mysql_connect')) {
                mysql_connect($this->getServerHostAddr(), $this->getServerUserName(), $this->getServerPassword(), true);
                $connectResource = @mysql_connect(
                    $this->getServerHostAddr(),
                    $this->getServerUserName(),
                    $this->getServerPassword(),
                    true
                );
                if (mysql_errno()) {
                    throw new \Exception(mysql_error($connectResource), mysql_errno($connectResource));
                }
                if (is_object($connectResource) || is_resource($connectResource)) {
                    $this->dataResource = $connectResource;
                    $this->db();
                    $connectSuccess = true;
                } else {
                    throw new \Exception("Connection to database failed without any proper reason", 500);
                }
            } else {
                throw new \Exception(
                    "You are trying to use a database driver that does not exist (mysql_connect)",
                    TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_TYPE_MYSQLD_NOT_EXIST
                );
            }

            return $connectSuccess;
        }

        /**
         * @param string $serverIdentifier
         * @param array $serverOptions
         * @param null $serverHostAddr
         * @param null $serverUsername
         * @param null $serverPassword
         *
         * @return bool|void
         * @throws \Exception
         */
        public function connect(
            $serverIdentifier = '',
            $serverOptions = [],
            $serverHostAddr = null,
            $serverUsername = null,
            $serverPassword = null
        ) {
            $this->setServerIdentifier($serverIdentifier);
            $this->setServerOptions($serverOptions);
            $this->setServerHostAddr($serverHostAddr);
            $this->setServerUserName($serverUsername);
            $this->setServerPassword($serverPassword);
            $driverInit = $this->prepareConnect();

            if ($driverInit == TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_IMPROVED) {
                return $this->CONNECT_MYSQL_IMPROVED();
            } else {
                if ($driverInit == TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_DEPRECATED) {
                    return $this->CONNECT_MYSQL_DEPRECATED();
                } else {
                    if ($driverInit == TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO) {
                        return $this->CONNECT_PDO();
                    } else {
                        throw new \Exception("Can not find any suitable database driver for MySQL", 500);
                    }
                }
            }
        }

        /**
         * Make this driver return objects instead of assoc
         *
         * @param bool $wantsObjectInsteadOfAssoc
         */
        public function setSqlObject($wantsObjectInsteadOfAssoc = false)
        {
            $this->getAsSqlObject = $wantsObjectInsteadOfAssoc;
        }

        /**
         * Connect to a schema (database)
         *
         * @param string $databaseName
         *
         * @return bool
         * @throws \Exception
         */
        public function db($databaseName = '')
        {
            if (empty($databaseName)) {
                $databaseName = $this->getDatabase();
            }
            if (empty($databaseName)) {
                return false;
            }
            if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_IMPROVED) {
                $setDb = mysqli_select_db($this->dataResource, $databaseName);
                if (mysqli_errno($this->dataResource)) {
                    throw new \Exception(mysqli_error($this->dataResource), mysqli_errno($this->dataResource));
                }

                return $setDb;
            } else {
                if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO) {
                    // Make sure that we are really able to change database before using the function
                    if (method_exists($this->dataResource, "select_db")) {
                        $setDb = $this->dataResource->select_db($databaseName);
                    } else {
                        $this->dataResource->query("use " . $databaseName);

                        return true;
                    }
                } else {
                    if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_DEPRECATED) {
                        $setDb = @mysql_select_db($databaseName, $this->dataResource);
                        if (mysql_errno($this->dataResource)) {
                            throw new \Exception(mysql_error($this->dataResource), mysql_errno($this->dataResource));
                        }
                    }
                }
            }

            return $setDb;
        }

        /**
         * Convert parameters that goes to mysql to things we can handle
         *
         * @param array $parameters
         *
         * @return string
         */
        private function getParameters($parameters = [])
        {
            return str_pad("", count($parameters), "s");
        }

        /**
         * mysqli driver prepare statement
         *
         * @param string $queryString
         * @param array $parameters
         * @param array $tests
         *
         * @return bool
         * @throws \Exception
         */
        private function QUERY_MYSQLI_PREPARE($queryString = '', $parameters = [], $tests = [])
        {
            $statementPrepare = mysqli_prepare($this->dataResource, $queryString);
            $resultArray = [];
            if (mysqli_errno($this->dataResource)) {
                throw new \Exception(mysqli_error($this->dataResource), mysqli_errno($this->dataResource));
            }
            if (!empty($statementPrepare)) {
                $refArgs = [$statementPrepare, $this->getParameters($parameters)];
                foreach ($parameters as $key => $value) {
                    $refArgs[] =& $parameters[$key];
                }
                // Having older versions than PHP 5.3 may cause problems.
                if (version_compare(phpversion(), "5.3.0", ">=")) {
                    // Bind variables to a prepared statement as parameters if any are set
                    if (count($parameters)) {
                        call_user_func_array("mysqli_stmt_bind_param", $refArgs);
                    }
                    // Execute the statement
                    if (mysqli_stmt_execute($statementPrepare)) {
                        // Prepare the result
                        $returnResult = [];
                        // Use get_result if it exists or fail over (this is specifically used in mysqlnd
                        if (method_exists(
                            $statementPrepare,
                            "get_result"
                        ) && (!isset($tests['META']) && !in_array('META', $tests))) {
                            $statementPrepareResult = $statementPrepare->get_result();
                            $this->mysqlPreparedResult = $statementPrepareResult;
                            if (is_object($statementPrepare)) {
                                $this->lastInsertId = mysqli_insert_id($this->dataResource);
                                if (isset($statementPrepare->affected_rows) && $statementPrepare->affected_rows > 0) {
                                    $this->mysqlAffectedRows = intval($statementPrepare->affected_rows);

                                    return true;
                                }

                                return false;
                            }

                            return false;
                        } else {
                            // Or do it the hard way.
                            // This one creates, per default, an assoc, but it is internally stored. Compared to get_result, this
                            // method steals more memory. However, bot of the methods should pass the fetch method, if the result should be
                            // properly returned.
                            $meta = $statementPrepare->result_metadata();
                            // If there is no meta, there is probably nothing to fetch either.
                            if (is_object($statementPrepare)) {
                                $this->lastInsertId = mysqli_insert_id($this->dataResource);
                                if (isset($statementPrepare->affected_rows) && $statementPrepare->affected_rows > 0) {
                                    $this->mysqlAffectedRows = intval($statementPrepare->affected_rows);

                                    return true;
                                }
                            }
                            if (is_object($meta)) {
                                while ($field = $meta->fetch_field()) {
                                    $resultArray[] = &$dataArray[$field->name];
                                }
                                call_user_func_array([$statementPrepare, 'bind_result'], $resultArray);
                                $resultRow = 0;
                                //$arrayCollection = array();
                                while ($statementPrepare->fetch()) {
                                    $array[$resultRow] = [];
                                    foreach ($dataArray as $dataKey => $dataValue) {
                                        $array[$resultRow][$dataKey] = $dataValue;
                                        //$arrayCollection[$dataKey] = $dataValue;
                                    }
                                    $resultRow++;
                                }
                                if (isset($array) && is_array($array) && count($array)) {
                                    $this->mysqlPreparedResult = $array;
                                    $this->mysqlAffectedRows = $resultRow;

                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        }
                    } else {
                        if (isset($statementPrepare->errno) && $statementPrepare->errno > 0) {
                            throw new \Exception($statementPrepare->error, $statementPrepare->errno);
                        }
                    }
                }
            } else {
                throw new \Exception("Query statement is empty", TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_EMPTY_STATEMENT);
            }
        }

        /**
         * I wish.
         *
         * @param string $queryString
         * @param array $parameters
         *
         * @throws \Exception
         */
        private function QUERY_MYSQL_PREPARE($queryString = '', $parameters = [])
        {
            throw new \Exception(
                "Prepared statements are not supported in this driver",
                TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_PREPARE_DEPRECATED
            );
        }

        /**
         * Unsupported PDO Prepare
         *
         * @param string $queryString
         * @param array $parameters
         *
         * @return bool
         * @throws \Exception
         */
        private function QUERY_PDO_PREPARE($queryString = '', $parameters = [])
        {
            $pdoExecResult = false;
            $statementPrepare = $this->dataResource->prepare($queryString);
            $pdoExecResult = $statementPrepare->execute($parameters);
            $this->mysqlAffectedRows = null;
            if (intval($this->dataResource->errorCode())) {
                $errorInfo = implode(", ", $this->dataResource->errorInfo());
                throw new \Exception($errorInfo, intval($this->dataResource->errorCode()));
            }

            return $pdoExecResult;
        }

        /**
         * Fetch data array from query with mysqli
         *
         * @param $mysqlResultObject
         *
         * @return array|null
         */
        private function FETCH_MYSQLI_ASSOC($mysqlResultObject)
        {
            return mysqli_fetch_assoc($mysqlResultObject);
        }

        /**
         * Fetch data object from query with mysqli
         *
         * @param $mysqlResultObject
         *
         * @return null|object
         */
        private function FETCH_MYSQLI_OBJECT($mysqlResultObject)
        {
            return mysqli_fetch_object($mysqlResultObject);
        }

        /**
         * Fetch data array from query with mysql deprecated driver
         *
         * @param $mysqlResultObject
         *
         * @return array
         */
        private function FETCH_MYSQL_DEPRECATED_ASSOC($mysqlResultObject)
        {
            return mysql_fetch_assoc($this->dataResource);
        }

        /**
         * Fetch data object from query with mysql deprecated driver
         *
         * @param $mysqlResultObject
         *
         * @return object|\stdClass
         */
        private function FETCH_MYSQL_DEPRECATED_OBJECT($mysqlResultObject)
        {
            return mysql_fetch_object($this->dataResource);
        }

        /**
         * Fetch data from query with PDO
         * @return mixed
         */
        private function FETCH_MYSQL_PDO()
        {
            if (method_exists($this->dataResource, 'fetch')) {
                return $this->dataResource->fetch();
            } else {
                return $this->mysqlPreparedResult->fetch();
            }
        }

        /**
         * RAW sql queries with mysqli driver
         *
         * @param string $queryString
         *
         * @return bool|\mysqli_result
         * @throws \Exception
         */
        private function QUERY_RAW_MYSQLI($queryString = '')
        {
            $queryResponse = mysqli_query($this->dataResource, $queryString);
            if (mysqli_errno($this->dataResource)) {
                $mysqli_error = mysqli_error($this->dataResource);
                $mysqli_errno = mysqli_errno($this->dataResource);
                throw new \Exception($mysqli_error, $mysqli_errno);
            }
            $this->mysqlPreparedResult = $queryResponse;
            $this->lastInsertId = mysqli_insert_id($this->dataResource);

            return $queryResponse;
        }

        /**
         * RAW sql queries with PDO driver
         *
         * @param string $queryString
         *
         * @return mixed
         */
        private function QUERY_RAW_PDO($queryString = '')
        {
            if ($this->getAsSqlObject) {
                $queryResponse = $this->dataResource->query($queryString, \PDO::FETCH_OBJ);
            } else {
                $queryResponse = $this->dataResource->query($queryString, \PDO::FETCH_ASSOC);
            }
            $this->mysqlPreparedResult = $queryResponse;
            $this->lastInsertId = $this->dataResource->lastInsertId();

            return $queryResponse;
        }

        /**
         * RAW sql queries with deprecated driver
         *
         * @param string $queryString
         *
         * @return resource
         * @throws \Exception
         */
        private function QUERY_RAW_DEPRECATED($queryString = '')
        {
            $queryResponse = mysql_query($queryString, $this->dataResource);
            if (mysql_errno($this->dataResource)) {
                $mysql_errno = mysql_errno($this->dataResource);
                $mysql_error = mysql_error($this->dataResource);
                throw new \Exception($mysql_error, $mysql_errno);
            }
            $this->lastInsertId = mysql_insert_id($this->dataResource);

            return $queryResponse;
        }

        /**
         * Insert raw SQL query
         *
         * @param string $queryString
         *
         * @return bool|\mysqli_result|resource
         * @throws \Exception
         */
        public function query_raw($queryString = '')
        {
            if (!empty($queryString)) {
                if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_IMPROVED) {
                    return $this->QUERY_RAW_MYSQLI($queryString);
                } else {
                    if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_DEPRECATED) {
                        return $this->QUERY_RAW_DEPRECATED($queryString);
                    } else {
                        if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO) {
                            return $this->QUERY_RAW_PDO($queryString);
                        }
                    }
                }
            } else {
                throw new \Exception(
                    "Can not parse empty query string",
                    TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_EMPTY_QUERY
                );
            }
        }

        /**
         * Prepare a query
         *
         * @param string $queryString
         * @param array $parameters Set to null, falling back to raw queries
         *
         * @return bool|\mysqli_result|resource|void
         * @throws Exception
         */
        public function query($queryString = '', $parameters = [])
        {
            if (is_null($parameters)) {
                return $this->query_raw($queryString);
            } else {
                return $this->query_prepare($queryString, $parameters);
            }
        }

        /**
         * Query first entry in database
         *
         * From v6.0, this is based on prepares
         *
         * @param string $queryString
         * @param array $parameters
         *
         * @return array|null
         * @throws Exception
         */
        public function query_first($queryString = '', $parameters = [])
        {
            if ($this->query_prepare($queryString, $parameters)) {
                return $this->fetch();
            }
        }

        /**
         * Query first intry in database, with prepare statement
         *
         * @param string $queryString
         * @param array $parameters
         *
         * @return array|null
         * @throws Exception
         */
        public function query_prepare_first($queryString = '', $parameters = [])
        {
            return $this->query_first();
        }

        /**
         * Handle query by prepare-methods
         *
         * @param string $queryString
         * @param array $parameters
         * @param array $tests
         *
         * @return bool|void
         * @throws \Exception
         */
        public function query_prepare($queryString = '', $parameters = [], $tests = [])
        {
            if (!is_array($parameters)) {
                $parameters = [];
            }
            if (empty($queryString)) {
                throw new \Exception(
                    "Can not parse empty query string",
                    TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_EMPTY_QUERY
                );
            }
            if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_IMPROVED) {
                return $this->QUERY_MYSQLI_PREPARE($queryString, $parameters, $tests);
            } else {
                if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_DEPRECATED) {
                    return $this->QUERY_MYSQL_PREPARE($queryString, $parameters, $tests);
                } else {
                    if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO) {
                        // This is highly unsupported
                        return $this->QUERY_PDO_PREPARE($queryString, $parameters, $tests);
                    } else {
                        throw new \Exception(
                            "Can not find any valid driver type",
                            TORNEVALL_DATABASE_EXCEPTIONS::DRIVER_TYPE_UNDEFINED
                        );
                    }
                }
            }
        }

        /**
         * Fetch a row from MySQL
         *
         * @param null $resource
         * @param bool $columnArray
         *
         * @return array|mixed|null|object|\stdClass
         */
        public function fetch($resource = null, $columnArray = true)
        {
            if (is_object($resource)) {
                $this->mysqlPreparedResult = $resource;
            }

            if (is_array($this->mysqlPreparedResult)) {
                return array_shift($this->mysqlPreparedResult);
            } else {
                if (is_object($this->mysqlPreparedResult)) {
                    if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_IMPROVED) {
                        if (!$this->getAsSqlObject) {
                            return $this->FETCH_MYSQLI_ASSOC($this->mysqlPreparedResult);
                        } else {
                            return $this->FETCH_MYSQLI_OBJECT($this->mysqlPreparedResult);
                        }
                    } else {
                        if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_DEPRECATED) {
                            if (!$this->getAsSqlObject) {
                                return $this->FETCH_MYSQL_DEPRECATED_ASSOC($this->mysqlPreparedResult);
                            } else {
                                return $this->FETCH_MYSQL_DEPRECATED_OBJECT($this->mysqlPreparedResult);
                            }
                        } else {
                            if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO) {
                                return $this->FETCH_MYSQL_PDO($this->mysqlPreparedResult);
                            }
                        }
                    }
                }
            }
        }

        /**
         * Compatibility mode for magic_quotes - DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 5.4.0
         *
         * This method will be passed only if necessary
         *
         * @link http://php.net/manual/en/security.magicquotes.php Security Magic Quotes
         *
         * @param null $injectionString
         *
         * @return null|string
         */
        private function escape_deprecated($injectionString = null)
        {
            if (version_compare(phpversion(), '5.3.0', '<=')) {
                if (function_exists('get_magic_quotes_gpc')) {
                    if (get_magic_quotes_gpc()) {
                        $injectionString = stripslashes($injectionString);
                    }
                }
            }

            return $injectionString;
        }

        /**
         * SQL escaping
         *
         * @param string $inputString
         *
         * @return mixed|string
         */
        public function escape($inputString = '')
        {
            // PGSQL: pg_escape_literal($this->escape_deprecated($injectionString))
            // MSSQL: preg_replace("[']", "''", $this->escape_deprecated($injectionString));

            if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_IMPROVED) {
                $returnString = @mysqli_real_escape_string($this->dataResource, $this->escape_deprecated($inputString));
            } else {
                if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_PDO) {
                    // The weakest way of stripping something
                    $quotedString = $this->dataResource->quote($inputString);
                    $returnString = preg_replace("@^'|'$@is", '', $quotedString);
                } else {
                    if ($this->getDriverType() === TORNEVALL_DATABASE_DRIVERS::DRIVER_MYSQL_IMPROVED) {
                        $returnString = @mysql_real_escape_string(
                            $this->dataResource,
                            $this->escape_deprecated($injectionString)
                        );
                    }
                }
            }

            return $returnString;
        }

        /**
         * SQL escaping
         *
         * @param $inputString
         *
         * @return mixed|string
         */
        public function injection($inputString)
        {
            return $this->escape($inputString);
        }

        /**
         * Return the last inserted id from a query
         * @return int
         */
        public function getLastInsertId()
        {
            return $this->lastInsertId;
        }

        /**
         * Get information about how may rows that was affected by a query
         * @return int
         */
        public function getAffectedRows()
        {
            return $this->mysqlAffectedRows;
        }
    }
}
