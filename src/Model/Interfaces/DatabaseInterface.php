<?php

namespace TorneLIB\Module\Interfaces;

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
     * @return mixed
     */
    public function setDatabase($schemaName);

    /**
     * @param int $portNumber
     * @return mixed
     */
    public function setServerPort($portNumber);

    /**
     * @return int
     */
    public function getServerPort();

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
