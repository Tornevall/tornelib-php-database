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

    public function __construct()
    {
        $this->CONFIG = new DatabaseConfig();
    }

    public function getServerType($identifierName = null)
    {
        // TODO: Implement getServerType() method.
    }

    /**
     * @param int $databaseType
     * @param null $identifierName
     * @return DatabaseConfig
     * @throws ExceptionHandler
     */
    public function setServerType($databaseType = Types::MYSQL, $identifierName = null)
    {
        switch ($databaseType) {
            case Types::MYSQL:
                $this->database = new MySQL();
                break;
            default:
                /** @var Types $databaseType */
                $this->isImplemented($databaseType);
                $this->database = new MySQL();
                break;
        }

        return $this->CONFIG->setDatabase($databaseType);
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
     * Retrieve the external real module.
     *
     * @return mixed
     * @since 6.1.0
     */
    public function getHandle()
    {
        return $this->database;
    }

    public function __call($name, $arguments)
    {
        if (is_null($this->database) && method_exists($this->database, $name)) {
            return call_user_func_array(
                [
                    $this->database,
                    $name,
                ],
                $arguments
            );
        }
    }

    /**
     * @return DatabaseConfig
     */
    public function getConfig()
    {
        // TODO: Implement getConfig() method.
    }

    /**
     * @param DatabaseConfig $databaseConfig
     * @return mixed
     */
    public function setConfig($databaseConfig)
    {
        // TODO: Implement setConfig() method.
    }

    /**
     * @return int
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
     * @return mixed
     */
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
     * @return string
     */
    public function getDatabase()
    {
        // TODO: Implement getDatabase() method.
    }

    /**
     * Prepare to enter schema/database. Prior name db()
     * @param $schemaName
     * @return $mixed
     */
    public function setDatabase($schemaName)
    {
        // TODO: Implement setDatabase() method.
    }

    /**
     * @param string $identifierName
     * @return $mixed
     */
    public function setIdentifier($identifierName)
    {
        // TODO: Implement setIdentifier() method.
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        // TODO: Implement getIdentifier() method.
    }

    /**
     * @param int $portNumber
     * @param null $identifierName
     * @return $mixed
     */
    public function setServerPort($portNumber, $identifierName = null)
    {
        // TODO: Implement setServerPort() method.
    }

    /**
     * @param null $identifierName
     * @return int
     */
    public function getServerPort($identifierName = null)
    {
        // TODO: Implement getServerPort() method.
    }

    /**
     * @param string $serverHost
     * @param null $identifierName
     * @return $mixed
     */
    public function setServerHost($serverHost, $identifierName = null)
    {
        // TODO: Implement setServerHost() method.
    }

    /**
     * @param null $identifierName
     * @return string
     */
    public function getServerHost($identifierName = null)
    {
        // TODO: Implement getServerHost() method.
    }

    /**
     * @param $userName
     * @param null $identifierName
     * @return $mixed
     */
    public function setServerUser($userName, $identifierName = null)
    {
        // TODO: Implement setServerUser() method.
    }

    /**
     * @param null $identifierName
     * @return string
     */
    public function getServerUser($identifierName = null)
    {
        // TODO: Implement getServerUser() method.
    }

    /**
     * @param $password
     * @param null $identifierName
     * @return $mixed
     */
    public function setServerPassword($password, $identifierName = null)
    {
        // TODO: Implement setServerPassword() method.
    }

    /**
     * @param null $identifierName
     * @return string
     */
    public function getServerPassword($identifierName = null)
    {
        // TODO: Implement getServerPassword() method.
    }

    /**
     * @param $serverOptions
     * @param null $identifierName
     * @return mixed
     */
    public function setServerOptions($serverOptions, $identifierName = null)
    {
        // TODO: Implement setServerOptions() method.
    }

    /**
     * @param null $identifierName
     * @return mixed
     */
    public function getServerOptions($identifierName = null)
    {
        // TODO: Implement getServerOptions() method.
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
