<?php

namespace TorneLIB\Model\Interfaces;

use TorneLIB\Module\Config\DatabaseConfig;

/**
 * Interface DatabaseInterface
 * @package TorneLIB\Module\Interfaces
 */
interface DatabaseInterface
{
    /**
     * DatabaseInterface constructor.
     *
     * For internal driver, 6.0-compatible content in constructor is set in this order:
     * serverIdentifier (string)
     * serverOptions = (array)
     * serverHostAddr = (string)
     * serverUsername = (string)
     * serverPassword = (string)
     */
    public function __construct();

    /**
     * @return DatabaseConfig
     */
    public function getConfig();

    /**
     * @param DatabaseConfig $databaseConfig
     * @return mixed
     */
    public function setConfig($databaseConfig);

    /**
     * @return int
     */
    public function getLastInsertId();

    /**
     * Connector. If no parameters are set, client will try defaults.
     *
     * @param string $serverIdentifier
     * @param array $serverOptions
     * @param string $serverHostAddr
     * @param string $serverUsername
     * @param string $serverPassword
     * @return mixed
     */
    public function connect(
        $serverIdentifier = 'localserver',
        $serverOptions = [],
        $serverHostAddr = '127.0.0.1',
        $serverUsername = 'username',
        $serverPassword = 'password'
    );

    /**
     * Prepare to enter schema/database. Prior name db()
     * @param $schemaName
     * @return $this
     */
    public function setDatabase($schemaName);

    /**
     * @return string
     */
    public function getDatabase();

    /**
     * @param string $identifierName
     * @return $this
     */
    public function setIdentifier($identifierName);

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @param int $portNumber
     * @param null $identifierName
     * @return $this
     */
    public function setServerPort($portNumber, $identifierName = null);

    /**
     * @param null $identifierName
     * @return int
     */
    public function getServerPort($identifierName = null);

    /**
     * @param string $serverHost
     * @param null $identifierName
     * @return $this
     */
    public function setServerHost($serverHost, $identifierName = null);

    /**
     * @param null $identifierName
     * @return string
     */
    public function getServerHost($identifierName = null);

    /**
     * @param $userName
     * @param null $identifierName
     * @return $this
     */
    public function setServerUser($userName, $identifierName = null);

    /**
     * @param null $identifierName
     * @return string
     */
    public function getServerUser($identifierName = null);

    /**
     * @param $password
     * @param null $identifierName
     * @return $this
     */
    public function setServerPassword($password, $identifierName = null);

    /**
     * @param null $identifierName
     * @return string
     */
    public function getServerPassword($identifierName = null);

    /**
     * setQuery (query)
     * @param string $queryString
     * @param array $parameters
     * @return mixed
     */
    public function setQuery($queryString, $parameters);

    /**
     * getFirst (prior: query_first)
     *
     * @param string $queryString
     * @param array $parameters
     * @return mixed
     */
    public function getFirst($queryString, $parameters);

    /**
     * getRow (prior: fetch first row)
     * @param $resource
     * @param bool $assoc
     * @return mixed
     */
    public function getRow($resource, $assoc = true);
}
