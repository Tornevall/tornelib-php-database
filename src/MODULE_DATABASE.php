<?php

namespace TorneLIB;

use TorneLIB\Exception\Constants;
use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\Model\Database\Types;
use TorneLIB\Model\Interfaces\DatabaseInterface;
use TorneLIB\Module\Config\DatabaseConfig;
use TorneLIB\Module\Database\Drivers\MySQL;

/**
 * Class MODULE_DATABASE
 * @package TorneLIB
 * @deprecated Fallback for 6.0 only
 * @since 6.0.0
 */
class MODULE_DATABASE implements DatabaseInterface
{
    private $database;
    private $CONFIG;

    /**
     * MODULE_DATABASE constructor.
     */
    public function __construct()
    {
        $this->CONFIG = new DatabaseConfig();
    }

    /**
     * @param null $identifierName
     * @return Types|void
     */
    public function getServerType($identifierName = null)
    {
        $this->CONFIG->getServerType($identifierName);
    }

    /**
     * Retrieve the external real module.
     *
     * @return mixed
     * @since 6.1.0
     * @noinspection PhpUnused
     */
    public function getHandle()
    {
        return $this->database;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        $return = null;
        if (!is_null($this->database) && method_exists($this->database, $name)) {
            $return = call_user_func_array(
                [
                    $this->database,
                    $name,
                ],
                $arguments
            );
        } elseif (!is_null($this->database) && method_exists($this->database->CONFIG, $name)) {
            $return = call_user_func_array(
                [
                    $this->database->CONFIG,
                    $name,
                ],
                $arguments
            );
        } elseif (method_exists($this->CONFIG, $name)) {
            $return = call_user_func_array(
                [
                    $this->CONFIG,
                    $name,
                ],
                $arguments
            );
        }
        return $return;
    }

    /**
     * @return DatabaseConfig
     */
    public function getConfig()
    {
        return $this->CONFIG;
    }

    /**
     * @param DatabaseConfig $databaseConfig
     * @return mixed
     */
    public function setConfig($databaseConfig)
    {
        $this->CONFIG = $databaseConfig;

        return $this;
    }

    /**
     * @return void
     */
    public function getLastInsertId()
    {
        // TODO: Implement getLastInsertId() method.
    }

    /**
     * Connector. If no parameters are set, client will try defaults.
     *
     * @param string $serverIdentifier
     * @param array $serverOptions
     * @param string $serverHostAddr
     * @param string $serverUsername
     * @param string $serverPassword
     * @param int $serverType
     * @param null $schemaName
     * @return mixed
     * @throws ExceptionHandler
     */
    public function connect(
        $serverIdentifier = 'localserver',
        $serverOptions = [],
        $serverHostAddr = '127.0.0.1',
        $serverUsername = 'username',
        $serverPassword = 'password',
        $serverType = Types::MYSQL,
        $schemaName = null
    ) {
        if (is_null($this->database)) {
            $this->setServerType($serverType, $serverIdentifier);
            $this->CONFIG->setDatabase($schemaName, $serverIdentifier);
        }

        return $this->database->connect(
            $serverIdentifier,
            $serverOptions,
            $serverHostAddr,
            $serverUsername,
            $serverPassword
        );
    }

    /**
     * @param int $databaseType
     * @param null $identifierName
     * @return DatabaseConfig
     * @throws ExceptionHandler
     */
    public function setServerType($databaseType = Types::MYSQL, $identifierName = null)
    {
        $this->isImplemented($databaseType);
        if ($databaseType === Types::MYSQL) {
            $this->database = new MySQL();
        }

        return $this->database->setServerType($databaseType, $identifierName);
    }

    /**
     * @param int $databaseType
     * @return bool
     * @throws ExceptionHandler
     * @since 6.1.0
     */
    private function isImplemented($databaseType = Types::MYSQL)
    {
        // As long as there is nothing but MySQL we'll throw this exception.
        if ($databaseType !== Types::MYSQL) {
            throw new ExceptionHandler(
                sprintf(
                    'Database type "%d" not implemented yet.',
                    $databaseType
                ),
                Constants::LIB_DATABASE_NOT_IMPLEMENTED
            );
        }

        return true;
    }

    /**
     * @param $identifierName
     * @param bool $throwable
     * @return string
     * @throws ExceptionHandler
     */
    public function getDatabase($identifierName, $throwable = true)
    {
        return $this->CONFIG->getDatabase($identifierName, $throwable);
    }

    /**
     * Prepare to enter schema/database. Prior name db()
     * @param $schemaName
     * @param null $identifierName
     * @return DatabaseConfig
     */
    public function setDatabase($schemaName, $identifierName = null)
    {
        return $this->CONFIG->setDatabase($schemaName, $identifierName);
    }

    /**
     * @param string $identifierName
     * @return DatabaseConfig
     */
    public function setIdentifier($identifierName)
    {
        return $this->CONFIG->setIdentifier($identifierName);
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
     * @return DatabaseConfig
     */
    public function setServerPort($portNumber, $identifierName = null)
    {
        return $this->CONFIG->setServerPort($portNumber, $identifierName);
    }

    /**
     * @param null $identifierName
     * @return int|string|null
     */
    public function getServerPort($identifierName = null)
    {
        return $this->CONFIG->getServerPort($identifierName);
    }

    /**
     * @param string $serverHost
     * @param null $identifierName
     * @return DatabaseConfig
     */
    public function setServerHost($serverHost, $identifierName = null)
    {
        return $this->CONFIG->setServerHost($serverHost, $identifierName);
    }

    /**
     * @param null $identifierName
     * @return string
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
     * @param $password
     * @param null $identifierName
     * @return DatabaseConfig
     */
    public function setServerPassword($password, $identifierName = null)
    {
        return $this->CONFIG->setServerPassword($password, $identifierName);
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

    /**
     * setQuery (query)
     * @param string $queryString
     * @param array $parameters
     * @return mixed
     */
    public function setQuery($queryString, $parameters)
    {
        // TODO: Implement setQuery() method.
    }

    /**
     * getFirst (prior: query_first)
     *
     * @param string $queryString
     * @param array $parameters
     * @return mixed
     */
    public function getFirst($queryString, $parameters)
    {
        // TODO: Implement getFirst() method.
    }

    /**
     * getRow (prior: fetch first row)
     * @param $resource
     * @param bool $assoc
     * @return mixed
     */
    public function getRow($resource, $assoc = true)
    {
        // TODO: Implement getRow() method.
    }
}
