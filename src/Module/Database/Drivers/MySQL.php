<?php

namespace TorneLIB\Module\Database\Drivers;

use TorneLIB\Exception\Constants;
use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\Model\Database\Drivers;
use TorneLIB\Model\Database\Types;
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

    private $preferredDriver = Drivers::DRIVER_OR_METHOD_UNAVAILABLE;

    /**
     * MySQL constructor.
     * @throws ExceptionHandler
     */
    public function __construct()
    {
        $this->CONFIG = new DatabaseConfig();

        $this->getInitializedDriver();
    }

    /**
     * @param null $forceDriver
     * @return int
     * @throws ExceptionHandler
     */
    private function getInitializedDriver($forceDriver = null)
    {
        if ((is_null($forceDriver) || $forceDriver === Drivers::DRIVER_MYSQL_IMPROVED) &&
            Security::getCurrentFunctionState('mysqli_connect', false)
        ) {
            $this->preferredDriver = Drivers::DRIVER_MYSQL_IMPROVED;
        } elseif ((is_null($forceDriver) || $forceDriver === Drivers::DRIVER_MYSQL_IMPROVED) &&
            Security::getCurrentFunctionState('mysql_connect', false)
        ) {
            $this->preferredDriver = Drivers::DRIVER_MYSQL_DEPRECATED;
        } elseif ((is_null($forceDriver) || $forceDriver === Drivers::DRIVER_MYSQL_PDO) &&
            Security::getCurrentClassState('PDO', false)
        ) {
            $this->preferredDriver = Drivers::DRIVER_MYSQL_PDO;
        } else {
            throw new ExceptionHandler(
                sprintf(
                    'No database drivers is available for %s.',
                    __CLASS__
                ),
                Constants::LIB_DATABASE_DRIVER_UNAVAILABLE
            );
        }

        return $this->preferredDriver;
    }

    /**
     * @return int
     */
    public function getPreferredDriver()
    {
        return $this->preferredDriver;
    }

    /**
     * @param int $preferDriver
     * @return $this
     * @throws ExceptionHandler
     */
    public function setPreferredDriver($preferDriver = Drivers::DRIVER_MYSQL_IMPROVED)
    {
        $this->getInitializedDriver($preferDriver);

        return $this;
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
     * @return int|string
     */
    public function getServerPort($identifierName = null)
    {
        return $this->CONFIG->getServerPort($identifierName);
    }

    /**
     * @param string $serverHost
     * @param null $identifierName
     * @return mixed|DatabaseConfig
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

    /**
     * @param $serverOptions
     * @param null $identifierName
     * @return mixed
     */
    public function setServerOptions($serverOptions, $identifierName = null)
    {
        return $this->CONFIG->setServerOptions($serverOptions, $identifierName);
    }

    /**
     * @param null $identifierName
     * @return mixed
     */
    public function getServerOptions($identifierName = null)
    {
        return $this->CONFIG->getServerOptions($identifierName);
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
