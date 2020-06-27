<?php

namespace TorneLIB\Module\Database\Drivers;

use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\Model\Interfaces\DatabaseInterface;
use TorneLIB\Module\Config\DatabaseConfig;
use TorneLIB\Utils\Security;

/**
 * Class MySQL
 * @package TorneLIB\Module\Database\Drivers
 */
class MySQL implements DatabaseInterface
{
    /**
     * @var DatabaseConfig $CONFIG
     */
    private $CONFIG;

    public function __construct()
    {
        $this->CONFIG = new DatabaseConfig();
    }

    public function getConfig()
    {
        return $this->CONFIG;
    }

    /**
     * @param DatabaseConfig $databaseConfig
     * @return $this|mixed
     */
    public function setConfig($databaseConfig)
    {
        $this->CONFIG = $databaseConfig;

        return $this;
    }

    public function getLastInsertId()
    {
        // TODO: Implement getLastInsertId() method.
    }

    public function connect(
        $serverIdentifier = 'localserver',
        $serverOptions = [],
        $serverHostAddr = '127.0.0.1',
        $serverUsername = 'username',
        $serverPassword = 'password'
    ) {
        // TODO: Implement connect() method.
    }

    /**
     * @param $schemaName
     * @return $this|mixed
     */
    public function setDatabase($schemaName)
    {
        $this->CONFIG->setDatabase($schemaName);

        return $this;
    }

    /**
     * @return string
     * @throws ExceptionHandler
     */
    public function getDatabase()
    {
        return $this->CONFIG->getDatabase();
    }

    /**
     * @param string $identifierName
     * @return $this
     */
    public function setIdentifier($identifierName)
    {
        $this->CONFIG->setIdentifier($identifierName);

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->CONFIG->getIdentifier();
    }


    public function setServerPort($portNumber)
    {
        // TODO: Implement setServerPort() method.
    }

    public function getServerPort()
    {
        // TODO: Implement getServerPort() method.
    }

    public function setQuery($queryString, $parameters)
    {
        // TODO: Implement setQuery() method.
    }

    public function getFirst($queryString, $parameters)
    {
        // TODO: Implement getFirst() method.
    }

    public function getRow($resource, $assoc = true)
    {
        // TODO: Implement getRow() method.
    }
}
