<?php

namespace TorneLIB\Module\Database\Drivers;

use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\Model\Database\Types;
use TorneLIB\Model\Interfaces\DatabaseInterface;
use TorneLIB\Module\Config\DatabaseConfig;

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
        $serverIdentifier = 'default',
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

    /**
     * @param int $portNumber
     * @param null $identifierName
     * @return $this
     * @since 6.1.0
     */
    public function setServerPort($portNumber, $identifierName = null)
    {
        $this->CONFIG->setServerPort($portNumber, $identifierName);

        return $this;
    }

    /**
     * @param null $identifierName
     * @return array
     */
    public function getServerPort($identifierName = null)
    {
        return $this->CONFIG->getServerPort($identifierName);
    }

    /**
     * @inheritDoc
     */
    public function setServerHost(
        $serverHost,
        $identifierName = null
    ) {
        return $this->CONFIG->setServerHost($serverHost, $identifierName);
    }

    /**
     * @inheritDoc
     */
    public function getServerHost($identifierName = null)
    {
        return $this->CONFIG->getServerHost($identifierName);
    }

    /**
     * @param $userName
     * @param null $identifierName
     * @return DatabaseConfig
     */
    public function setServerUser($userName, $identifierName = null)
    {
        return $this->CONFIG->setServerUser($userName, $identifierName);
    }

    /**
     * @param null $identifierName
     * @return string
     */
    public function getServerUser($identifierName = null)
    {
        return $this->CONFIG->getServerUser($identifierName);
    }

    /**
     * @param $userName
     * @param null $identifierName
     * @return DatabaseConfig
     */
    public function setServerPassword($userName, $identifierName = null)
    {
        return $this->CONFIG->setServerPassword($userName, $identifierName);
    }

    /**
     * @param null $identifierName
     * @return string
     */
    public function getServerPassword($identifierName = null)
    {
        return $this->CONFIG->getServerPassword($identifierName);
    }

    /**
     * @param int $serverType
     * @param null $identifierName
     * @return DatabaseConfig
     */
    public function setServerType($serverType = Types::MYSQL, $identifierName = null)
    {
        return $this->CONFIG->setServerType($serverType, $identifierName);
    }

    /**
     * @param null $identifierName
     * @return string
     */
    public function getServerType($identifierName = null)
    {
        return $this->CONFIG->getServerType($identifierName);
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
