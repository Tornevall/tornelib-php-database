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
 */

namespace TorneLIB;

require_once __DIR__ . "/tornevall_database_abstracts.php";
require_once __DIR__ . "/tornevall_database_interface.php";
require_once __DIR__ . "/tornevall_database_driver_mysql.php";

if (!class_exists('MODULE_DATABASE') && !class_exists('TorneLIB\MODULE_DATABASE_DEPRECATED')) {

    /**
     * Class TorneLIB_Database
     *
     * Making sure autotests are running:
     *  apt-get install php-mysqli
     *
     * @package TorneLIB
     * @version 6.0.4
     */
    class MODULE_DATABASE_DEPRECATED implements libdriver_database_interface
    {

        /** @var Identifier name */
        private $SERVER_IDENTIFIER;
        /** @var Options in */
        private $SERVER_OPTIONS;
        /** @var Hostname or address */
        private $SERVER_HOST_ADDRESS;
        /** @var Server username */
        private $SERVER_USER_NAME;
        /** @var Server password */
        private $SERVER_USER_PASSWORD;
        /** @var int Server port name, defaults to mysql */
        private $SERVER_PORT = 3306;
        /** @var Predefined datbase name to connect to */
        private $SERVER_DATABASE_NAME;

        /** @var TORNEVALL_DATABASE_TYPES */
        private $SERVER_TYPE;
        /** @var TORNEVALL_DATABASE_DRIVERS */
        private $SERVER_DRIVER_TYPE;

        /** @var libdriver_database_interface */
        private $SERVER_RESOURCE;
        /** @var resource $SERVER_RESOURCE_CONNECTOR */
        private $SERVER_RESOURCE_CONNECTOR;
        /** @var bool Specifies if the database initializer should recreate resource for each new instance - normally we'd like to keep the old resource so it will be possible to enforce drivers */
        private $SERVER_RESOURCE_FORCE_NEW = false;

        /** @var bool If this value is set to true, query_first results will pop the response out of the array if there is only one returned column */
        private $SINGLE_COLUMN_POPPABLE = false;

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
         * @param bool $connect Connect on construct
         * @throws \Exception
         */
        function __construct(
            $serverIdentifier = '',
            $serverOptions = array(),
            $serverHostAddr = null,
            $serverUsername = null,
            $serverPassword = null,
            $serverType = TORNEVALL_DATABASE_TYPES::MYSQL,
            $databaseName = '',
            $connect = false
        ) {
            if (is_null($serverOptions)) {
                $serverOptions = array();
            }
            $this->setServerIdentifier($serverIdentifier);
            $this->setServerOptions($serverOptions);
            $this->setServerHostAddr($serverHostAddr);
            $this->setServerUserName($serverUsername);
            $this->setServerPassword($serverPassword);
            $this->setServerType($serverType);
            $this->SERVER_TYPE = $serverType;
            if (!empty($databaseName)) {
                $this->setDatabase($databaseName);
            }

            if (!empty($serverHostAddr) && !empty($serverUsername) && !empty($serverPassword) && $connect) {
                $this->connect($this->getServerIdentifier(), $this->getServerOptions(), $this->getServerHostAddr(),
                    $this->getServerUserName(), $this->getServerPassword());
                if (!empty($databaseName)) {
                    $this->db($databaseName);
                }
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
         * @throws \Exception
         */
        private function initializeDatabaseDriver($newDriver = false)
        {
            if ((is_object($this->SERVER_RESOURCE) || is_resource($this->SERVER_RESOURCE))) {
                if (!$newDriver) {
                    $this->SERVER_RESOURCE_FORCE_NEW = $newDriver;

                    return true;
                }
            }
            if ($this->SERVER_TYPE === TORNEVALL_DATABASE_TYPES::MYSQL) {
                $this->SERVER_RESOURCE = new libdriver_mysql($this->getServerIdentifier(), $this->getServerOptions(),
                    $this->getServerHostAddr(), $this->getServerUserName(), $this->getServerPassword());
                $this->SERVER_RESOURCE->setPort($this->getPort());
                $this->SERVER_RESOURCE->setDatabase($this->getDatabase());
                if ($this->SERVER_DRIVER_TYPE !== TORNEVALL_DATABASE_DRIVERS::DRIVER_TYPE_NONE) {
                    if (method_exists($this->SERVER_RESOURCE, "setDriverType")) {
                        $this->setDriverType($this->SERVER_DRIVER_TYPE);
                    }
                }
            }
            if (is_object($this->SERVER_RESOURCE) || is_resource($this->SERVER_RESOURCE)) {
                return true;
            }
        }

        /**
         * If column array in a query_first is alone, the response will be poppable when this option is enabled
         * @param bool $poppable
         */
        public function setQueryFirstPoppable($poppable = true)
        {
            $this->SINGLE_COLUMN_POPPABLE = $poppable;
        }

        public function getQueryFirstPoppable()
        {
            return $this->SINGLE_COLUMN_POPPABLE;
        }


        /**
         * Identify current server with name
         *
         * @param string $serverIdentifier
         */
        public function setServerIdentifier($serverIdentifier = '')
        {
            $this->SERVER_IDENTIFIER = !empty($serverIdentifier) ? $serverIdentifier : "default";
        }

        /**
         * Get server name (identification)
         * @return Identifier
         */
        public function getServerIdentifier()
        {
            return $this->SERVER_IDENTIFIER;
        }

        /**
         * Set special options for database
         *
         * @param array $serverOptions
         */
        public function setServerOptions($serverOptions = array())
        {
            if (is_array($serverOptions) && count($serverOptions)) {
                $this->SERVER_OPTIONS = $serverOptions;
            } else {
                $this->SERVER_OPTIONS = array();
            }
        }

        /**
         * Get currrent set server options
         * @return Options
         */
        public function getServerOptions()
        {
            return $this->SERVER_OPTIONS;
        }

        /**
         * Set up host/addr to database server
         *
         * @param string $serverHostAddr
         */
        public function setServerHostAddr($serverHostAddr = '')
        {
            if (!empty($serverHostAddr)) {
                $this->SERVER_HOST_ADDRESS = $serverHostAddr;
            }
        }

        /**
         * Get current set host/addr to database server
         * @return Hostname
         */
        public function getServerHostAddr()
        {
            return $this->SERVER_HOST_ADDRESS;
        }

        /**
         * Set username credentials
         *
         * @param string $serverUsername
         */
        public function setServerUserName($serverUsername = '')
        {
            if (!empty($serverUsername)) {
                $this->SERVER_USER_NAME = $serverUsername;
            }
        }

        /**
         * Get current username credentials
         * @return Server
         */
        public function getServerUserName()
        {
            return $this->SERVER_USER_NAME;
        }

        /**
         * Set current password credentials
         *
         * @param string $serverPassword
         */
        public function setServerPassword($serverPassword = '')
        {
            if (!empty($serverPassword)) {
                $this->SERVER_USER_PASSWORD = $serverPassword;
            }
        }

        /**
         * Get current password credentials
         * @return Server
         */
        public function getServerPassword()
        {
            return $this->SERVER_USER_PASSWORD;
        }

        /**
         * Change default connector port
         *
         * @param int $serverPortNumber
         */
        public function setPort($serverPortNumber = 3306)
        {
            if (!empty($serverPortNumber) && is_numeric($serverPortNumber)) {
                $this->SERVER_PORT = $serverPortNumber;
            }
        }

        /**
         * Get the default connector port
         *
         * @return int
         */
        public function getPort()
        {
            return $this->SERVER_PORT;
        }

        /**
         * Preconfigure database to connect to
         *
         * @param string $databaseName
         */
        public function setDatabase($databaseName = '')
        {
            if (!empty($databaseName)) {
                $this->SERVER_DATABASE_NAME = $databaseName;
                if (!empty($this->SERVER_RESOURCE) && method_exists($this->SERVER_RESOURCE, "setDatabase")) {
                    $this->SERVER_RESOURCE->setDatabase($this->SERVER_DATABASE_NAME);
                }
            }
        }

        /**
         * Get preconfigured database to connect to
         *
         * @return mixed
         */
        public function getDatabase()
        {
            return $this->SERVER_DATABASE_NAME;
        }
        //// INTERFACE SETUP END

        //// BASIC FUNCTIONS

        /**
         * @param $ownServerDriver libdriver_database_interface
         */
        public function setServerDriver($ownServerDriver)
        {
            $this->SERVER_RESOURCE = $ownServerDriver;
        }

        /**
         * Set up which driver that should be used on connection
         *
         * @param int $serverType
         */
        public function setServerType($serverType = TORNEVALL_DATABASE_TYPES::MYSQL)
        {
            $this->SERVER_TYPE = $serverType;
        }

        /**
         * @return int|TORNEVALL_DATABASE_TYPES
         */
        public function getServerType()
        {
            return $this->SERVER_TYPE;
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
         * @throws \Exception
         */
        public function connect(
            $serverIdentifier = '',
            $serverOptions = array(),
            $serverHostAddr = null,
            $serverUsername = null,
            $serverPassword = null,
            $forceNew = false
        ) {
            $this->setServerIdentifier($serverIdentifier);
            $this->setServerOptions($serverOptions);
            $this->setServerHostAddr($serverHostAddr);
            $this->setServerUserName($serverUsername);
            $this->setServerPassword($serverPassword);
            $this->initializeDatabaseDriver($forceNew);

            $useDatabase = $this->getDatabase();
            if (!empty($useDatabase)) {
                $this->SERVER_RESOURCE->setDatabase();
            }

            $this->SERVER_RESOURCE_CONNECTOR = $this->SERVER_RESOURCE->connect($this->getServerIdentifier(), $this->getServerOptions(),
                $this->getServerHostAddr(), $this->getServerUserName(), $this->getServerPassword());

            return $this->SERVER_RESOURCE_CONNECTOR;
        }

        /**
         * Make sure there is a connection before running any queries (requires that someone has set up the connection properly)
         * @return bool|mixed
         * @throws \Exception
         */
        private function checkConnection()
        {
            $result = false;
            if (!is_object($this->SERVER_RESOURCE) || !is_resource($this->SERVER_RESOURCE)) {
                $result = $this->initializeDatabaseDriver($this->SERVER_RESOURCE_FORCE_NEW);
            }
            return $result;
        }

        /**
         * Change database when action is supported
         * @param string $databaseName
         * @return libdriver_database_interface
         * @throws \Exception
         */
        public function db($databaseName = '')
        {
            /** @var libdriver_database_interface $result */
            $result = null;
            if ($this->checkConnection()) {
                $result = $this->SERVER_RESOURCE->db($databaseName);
            }
            return $result;
        }

        /**
         * Prepare strings for injection protection
         *
         * @param null $injectionString
         */
        public function escape($injectionString = null)
        {
            return $this->SERVER_RESOURCE->escape($injectionString);
        }

        /**
         * Return result after query
         * @param null $resource
         * @param bool $columnArray
         * @return libdriver_database_interface
         * @throws \Exception
         */
        public function fetch($resource = null, $columnArray = true)
        {
            /** @var libdriver_database_interface $result */
            $result = null;
            if ($this->checkConnection()) {
                $result = $this->SERVER_RESOURCE->fetch($resource, $columnArray);
            }
            return $result;
        }

        /**
         * Get last insert id if INSERT/REPLACE has been runned
         * @return int|libdriver_database_interface
         * @throws \Exception
         */
        public function getLastInsertId()
        {
            /** @var libdriver_database_interface $result */
            $result = null;
            if ($this->checkConnection()) {
                $result = (int)$this->SERVER_RESOURCE->getLastInsertId();
            }
            return $result;
        }

        /**
         * Query database (default uses queries based on prepare)
         *
         * @param string $queryString
         * @param array $parameters
         * @return libdriver_database_interface
         * @throws \Exception
         */
        public function query($queryString = '', $parameters = array())
        {
            /** @var libdriver_database_interface $result */
            $result = null;
            if ($this->checkConnection()) {
                $result = $this->SERVER_RESOURCE->query($queryString, $parameters);
            }
            return $result;
        }

        /**
         * Query method that works like the regular query function call but it also returns the first row found in database
         *
         * @param string $queryString
         * @param array $parameters
         * @param bool $singleValueIsPopped
         * @return libdriver_database_interface
         * @throws \Exception
         */
        public function query_first($queryString = '', $parameters = array(), $singleValueIsPopped = false)
        {
            /** @var libdriver_database_interface $result */
            $result = null;
            if ($this->checkConnection()) {
                $result = $this->SERVER_RESOURCE->query_first($queryString, $parameters);
            }
            if (($singleValueIsPopped || $this->getQueryFirstPoppable()) && (is_array($result) && count($result))) {
                return array_pop($result);
            }
            return $result;
        }

        /**
         * Prepare a query yourself (default queries almost always runs through this)
         *
         * @param string $queryString
         * @param array $parameters
         * @param array $tests
         * @return libdriver_database_interface
         * @throws \Exception
         */
        public function query_prepare($queryString = '', $parameters = array(), $tests = array())
        {
            /** @var libdriver_database_interface $result */
            $result = null;
            if ($this->checkConnection()) {
                $result = $this->SERVER_RESOURCE->query_prepare($queryString, $parameters, $tests);
            }
            return $result;
        }

        /**
         * Just lite query_prepare and query_first: handmade return-first-query-method
         *
         * @param string $queryString
         * @param array $parameters
         * @return libdriver_database_interface
         * @throws \Exception
         */
        public function query_prepare_first($queryString = '', $parameters = array())
        {
            /** @var libdriver_database_interface $result */
            $result = null;
            if ($this->checkConnection()) {
                $result = $this->SERVER_RESOURCE->query_prepare_first($queryString, $parameters);
            }
            return $result;
        }

        /**
         * A raw query straight into the database where you are completely on your own
         * @param string $queryString
         * @return libdriver_database_interface
         * @throws \Exception
         */
        public function query_raw($queryString = '')
        {
            /** @var libdriver_database_interface $result */
            $result = null;
            if ($this->checkConnection()) {
                $result = $this->SERVER_RESOURCE->query_raw($queryString);
            }
            return $result;
        }

        /**
         * Makes all by primary driver unsupported actions from the interface runnable
         * @param $name
         * @param $arguments
         * @return mixed|libdriver_database_interface
         * @throws \Exception
         */
        public function __call($name, $arguments)
        {
            /** @var libdriver_database_interface $result */
            $result = null;
            if (empty($this->SERVER_RESOURCE)) {
                $this->initializeDatabaseDriver($this->SERVER_RESOURCE_FORCE_NEW);
            }
            $result = @call_user_func_array(array($this->SERVER_RESOURCE, $name), $arguments);

            return $result;
        }
    }
}

if (!class_exists('TorneLIB_Database') && !class_exists('TorneLIB\TorneLIB_DatabaseDEPRECATED')) {
    class TorneLIB_DatabaseDEPRECATED extends MODULE_DATABASE_DEPRECATED
    {
    }
}
