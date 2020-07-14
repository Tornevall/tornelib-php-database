<?php

namespace TorneLIB;

use Exception;
use TorneLIB\Exception\Constants;
use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\Helpers\DataHelper;
use TorneLIB\Helpers\Version;
use TorneLIB\Model\Database\Types;
use TorneLIB\Model\Interfaces\DatabaseInterface;
use TorneLIB\Module\Config\DatabaseConfig;
use TorneLIB\Module\Database\Drivers\MySQL;

try {
    Version::getRequiredVersion();
} catch (Exception $e) {
    echo $e->getMessage();
    die;
}

/**
 * Class MODULE_DATABASE
 * @package TorneLIB
 * @deprecated Fallback for 6.0 only
 * @since 6.0.0
 */
class MODULE_DATABASE implements DatabaseInterface
{
    /**
     * @var mixed
     * @since 6.1.0
     */

    private $database;
    /**
     * @var DatabaseConfig
     * @since 6.1.0
     */
    private $CONFIG;

    /**
     * MODULE_DATABASE constructor.
     * @since 6.1.0
     */
    public function __construct()
    {
        $this->CONFIG = new DatabaseConfig();
    }

    /**
     * @since 6.1.0
     */
    public function __destruct()
    {
        $identifiers = $this->CONFIG->getIdentifiers();

        foreach ($identifiers as $identifierName) {
            DataHelper::closeConnection($this->CONFIG, $identifierName);
        }
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
     * Retrieve the real module.
     * @return mixed
     * @since 6.1.0
     * @deprecated Use getConnection.
     */
    public function getHandle()
    {
        return $this->getConnection();
    }

    /**
     * Retrieve the real module.
     * @return mixed
     * @since 6.1.0
     */
    public function getConnection()
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
        $dbExist = !is_null($this->database);
        if ($dbExist && method_exists($this->database, $name)) {
            $return = call_user_func_array(
                [
                    $this->database,
                    $name,
                ],
                $arguments
            );
        } elseif ($dbExist &&
            method_exists($this->database->CONFIG, $name)
        ) {
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
     * @param $inputString
     * @param null $identifierName
     * @return mixed
     * @since 6.0.0
     * @deprecated Escaping through datahelper is deprecated and should be avoided.
     * @noinspection PhpDeprecationInspection
     */
    public function escape($inputString, $identifierName = null)
    {
        if (method_exists($this->database, 'escape')) {
            $return = $this->database->escape($inputString, $identifierName);
        } else {
            try {
                $return = DataHelper::getEscaped(
                    $inputString,
                    $this->CONFIG->getPreferredDriver($identifierName),
                    $this->CONFIG->getConnection($identifierName)
                );
            } catch (Exception $e) {
                $return = (new DataHelper())->getEscapeDeprecated($inputString);
            }
        }

        return $return;
    }

    /**
     * @param $inputString
     * @param $identifierName
     * @return mixed
     * @since 6.0.0
     * @deprecated Escaping through datahelper is deprecated and should be avoided.
     * @noinspection PhpDeprecationInspection
     */
    public function injection($inputString, $identifierName)
    {
        return $this->escape($inputString, $identifierName);
    }

    /**
     * @param null $identifierName
     * @return int
     * @since 6.1.0
     */
    public function getLastInsertId($identifierName = null)
    {
        $return = 0;
        if (method_exists($this->database, 'getLastInsertId')) {
            $return = $this->database->getLastInsertId($identifierName);
        }
        return $return;
    }

    /**
     * @param null $identifier
     * @return mixed
     * @since 6.1.0
     */
    public function getAffectedRows($identifier = null)
    {
        $return = 0;
        if (method_exists($this->database, 'getAffectedRows')) {
            $return = $this->database->getAffectedRows($identifier);
        }
        return $return;
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
        $serverIdentifier = 'default',
        $serverOptions = [],
        $serverHostAddr = '127.0.0.1',
        $serverUsername = 'username',
        $serverPassword = 'password',
        $serverType = Types::MYSQL,
        $schemaName = null
    ) {
        // Fix developers mistakes.
        if (empty($serverIdentifier)) {
            $serverIdentifier = 'default';
        }
        if (!is_array($serverOptions)) {
            if (!empty($serverOptions)) {
                $serverOptions = (array)$serverOptions;
            } else {
                $serverOptions = [];
            }
        }

        // Initialize proper database if it not already exist.
        if (is_null($this->database)) {
            $this->setServerType($serverType, $serverIdentifier);
        }
        $this->setPreferredDriverOverrider($serverType, $serverIdentifier);

        $return = $this->database->connect(
            $serverIdentifier,
            $serverOptions,
            $serverHostAddr,
            $serverUsername,
            $serverPassword
        );
        $this->setPreparedEarlySchema($schemaName, $serverIdentifier);

        return $return;
    }

    /**
     * Prepare schema at initialization state.
     * @param $schemaName
     * @param $serverIdentifier
     * @return bool
     */
    private function setPreparedEarlySchema($schemaName, $serverIdentifier)
    {
        $return = false;
        if (!empty($schemaName)) {
            $this->CONFIG->setDatabase($schemaName, $serverIdentifier);
        }
        try {
            $currentSchema = $this->CONFIG->getDatabase($serverIdentifier);
            $return = $this->database->setDatabase($currentSchema, $serverIdentifier);

        } catch (Exception $schemaException) {
        }

        return $return;
    }

    /**
     * Make sure overriders are triggered properly.
     * @param $serverType
     * @param $serverIdentifier
     * @return MODULE_DATABASE
     */
    private function setPreferredDriverOverrider($serverType, $serverIdentifier)
    {
        if ($serverType === Types::MYSQL) {
            // Make sure we fetch overriders.
            $this->database->setPreferredDriver(
                $this->CONFIG->getPreferredDriver($serverIdentifier),
                $serverIdentifier
            );
        }

        return $this;
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
     * Mostly for mysql where more drivers (mysqli, pdo, etc) than one is available.
     * @param $preferredDriver
     * @param null $identifierName
     * @return mixed
     */
    public function setPreferredDriver($preferredDriver, $identifierName = null)
    {
        $this->CONFIG->setPreferredDriver($preferredDriver, $identifierName);

        return $this;
    }

    /**
     * @param null $identifierName
     * @return mixed
     * @since 6.1.0
     */
    public function getPreferredDriver($identifierName = null)
    {
        return $this->CONFIG->getPreferredDriver($identifierName);
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
    public function setQuery($queryString, $parameters = [])
    {
        return $this->database->setQuery($queryString, $parameters);
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
        return $this->database->getFirst($queryString, $parameters);
    }

    /**
     * getRow (prior: fetch first row)
     * @param $resource
     * @param bool $assoc
     * @return mixed
     */
    public function getRow($resource, $assoc = true)
    {
        return $this->database->getRow($resource, $assoc);
    }
}
