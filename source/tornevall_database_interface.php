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
 * @version 6.0.0
 *
 */

namespace TorneLIB;

if (!interface_exists('libdriver_database_interface') && !interface_exists('TorneLIB\libdriver_database_interface')) {
    /**
     * Interface libdriver_interface Default set up interface for database drivers
     * @package TorneLIB
     */
    interface libdriver_database_interface
    {
        function __construct(
            $serverIdentifier = '',
            $serverOptions = array(),
            $serverHostAddr = null,
            $serverUsername = null,
            $serverPassword = null
        );

        public function setServerIdentifier($serverIdentifier = '');

        public function getServerIdentifier();

        public function setServerOptions($serverOptions = array());

        public function getServerOptions();

        public function setServerHostAddr($serverHostAddr = '');

        public function getServerHostAddr();

        public function setServerUserName($serverUsername = '');

        public function getServerUserName();

        public function setServerPassword($serverPassword = '');

        public function getServerPassword();

        public function setPort($serverPortNumber = 3306);

        public function getPort();

        public function setDatabase($databaseName = '');

        public function getDatabase();

        public function connect(
            $serverIdentifier = '',
            $serverOptions = array(),
            $serverHostAddr = null,
            $serverUsername = null,
            $serverPassword = null
        );

        public function db($databaseName = '');

        public function getLastInsertId();

        public function query_raw($queryString = '');

        public function query($queryString = '', $parameters = array());

        public function query_first($queryString = '', $parameters = array());

        public function query_prepare_first($queryString = '', $parameters = array());

        public function query_prepare($queryString = '', $parameters = array(), $tests = array());

        public function fetch($resource = null, $columnArray = true);

        public function escape($injectionString = null);
    }
}