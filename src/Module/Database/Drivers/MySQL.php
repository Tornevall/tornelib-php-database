<?php

/** @noinspection PhpDeprecationInspection */

/** @noinspection PhpComposerExtensionStubsInspection */

namespace TorneLIB\Module\Database\Drivers;

use PDO;
use PDOException;
use TorneLIB\Config\Flag;
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

    /**
     * @var $initDriver Indicates if driver is really initialized.
     */
    private $initDriver;

    /**
     * @var bool $pdoSql True if PDO can use MySQL.
     */
    private $pdoSql = false;

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
     * @param null $identifier
     * @return int
     * @throws ExceptionHandler
     */
    private function getInitializedDriver($forceDriver = null, $identifier = null)
    {
        $this->initDriver[$this->CONFIG->getCurrentIdentifier($identifier)] = true;

        if ((is_null($forceDriver) || $forceDriver === Drivers::DRIVER_MYSQL_IMPROVED) &&
            Security::getCurrentFunctionState('mysqli_connect', false)
        ) {
            $this->CONFIG->setPreferredDriver(Drivers::DRIVER_MYSQL_IMPROVED, $identifier);
        } elseif ((is_null($forceDriver) || $forceDriver === Drivers::DRIVER_MYSQL_DEPRECATED) &&
            Security::getCurrentFunctionState('mysql_connect', false)
        ) {
            $this->CONFIG->setPreferredDriver(Drivers::DRIVER_MYSQL_DEPRECATED, $identifier);
        } elseif ((is_null($forceDriver) || $forceDriver === Drivers::DRIVER_MYSQL_PDO) &&
            Security::getCurrentClassState('PDO', false) &&
            $this->getCanPdo()
        ) {
            $this->CONFIG->setPreferredDriver(Drivers::DRIVER_MYSQL_PDO, $identifier);
        } else {
            throw new ExceptionHandler(
                sprintf(
                    'No database drivers is available for %s.',
                    __CLASS__
                ),
                Constants::LIB_DATABASE_DRIVER_UNAVAILABLE
            );
        }

        return $this->CONFIG->getPreferredDriver();
    }

    /**
     * @return bool
     */
    private function getCanPdo()
    {
        $return = false;
        if (Security::getCurrentClassState('PDO', false)) {
            $pdoDriversStatic = PDO::getAvailableDrivers();
            if (in_array('mysql', $pdoDriversStatic, true)) {
                $return = true;
            }
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

    /**
     * @param string $serverIdentifier
     * @param array $serverOptions
     * @param string $serverHostAddr
     * @param string $serverUsername
     * @param string $serverPassword
     * @return mixed|void
     * @throws ExceptionHandler
     */
    public function connect(
        $serverIdentifier = 'default',
        $serverOptions = [],
        $serverHostAddr = '127.0.0.1',
        $serverUsername = 'tornelib',
        $serverPassword = 'tornelib1337'
    ) {
        $return = null;

        $useIdentifier = $this->CONFIG->getCurrentIdentifier($serverIdentifier);

        if (!isset($this->initDriver[$useIdentifier])) {
            $this->getInitializedDriver(null, $useIdentifier);
        }

        // Configure current connection.
        $this->setServer(
            $useIdentifier,
            $serverOptions,
            $serverHostAddr,
            $serverUsername,
            $serverPassword
        );

        switch ($this->getPreferredDriver($useIdentifier)) {
            case Drivers::DRIVER_MYSQL_IMPROVED:
                $return = $this->connect_mysqli($useIdentifier);
                break;
            case Drivers::DRIVER_MYSQL_DEPRECATED:
                $return = $this->connect_mysql($useIdentifier);
                break;
            case Drivers::DRIVER_MYSQL_PDO:
                $return = $this->connect_pdo($useIdentifier);
                break;

            default:
                throw new ExceptionHandler(
                    sprintf(
                        '%s error in %s: could not find any proper driver to connect with.',
                        __FUNCTION__,
                        __CLASS__
                    )
                );
                break;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if (Flag::getFlag('SQLCHAIN')) {
            return $this;
        }

        return $return;
    }

    /**
     * @param $identifier
     * @param $options
     * @param $serverAddr
     * @param $serverUser
     * @param $serverPassword
     * @return $this
     */
    private function setServer($identifier, $options, $serverAddr, $serverUser, $serverPassword)
    {
        $this->CONFIG->setIdentifier($identifier);
        $this->CONFIG->setServerOptions($options, $identifier);
        $this->CONFIG->setServerHost($serverAddr, $identifier);
        $this->CONFIG->setServerUser($serverUser, $identifier);
        $this->CONFIG->setServerPassword($serverPassword, $identifier);

        return $this;
    }

    /**
     * @param null $identifier
     * @return int
     * @noinspection PhpUnused
     */
    public function getPreferredDriver($identifier = null)
    {
        return $this->CONFIG->getPreferredDriver($identifier);
    }

    /**
     * @param $identifier
     * @return bool
     * @throws ExceptionHandler
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    private function connect_mysqli($identifier)
    {
        /** @var array|\mysqli $connection */
        $connection = @mysqli_connect(
            $this->getServerHost($identifier),
            $this->getServerUser($identifier),
            $this->getServerPassword($identifier),
            $this->getDatabase($identifier, false),
            $this->getServerPort($identifier)
        );

        if ((array)$connection) {
            $this->CONFIG->setConnection(
                $connection,
                $identifier
            );
        }

        $this->getConnectError(mysqli_connect_error(), mysqli_connect_errno(), __FUNCTION__);
        $this->getConnectError(mysqli_error($connection), mysqli_errno($connection), __FUNCTION__);
        $this->setLocalServerOptions($identifier);

        return is_object($connection);
    }

    /**
     * @inheritDoc
     */
    public function getServerHost($identifierName = null)
    {
        return $this->CONFIG->getServerHost($identifierName);
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
     * @param null $identifierName
     * @return string
     */
    public function getServerPassword($identifierName = null)
    {
        return $this->CONFIG->getServerPassword($identifierName);
    }

    /**
     * @param $identifierName
     * @param bool $throwable
     * @return string
     * @throws ExceptionHandler
     */
    public function getDatabase($identifierName = null, $throwable = false)
    {
        return $this->CONFIG->getDatabase($identifierName, $throwable);
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
     * @param $message
     * @param $code
     * @param $fromFunction
     * @throws ExceptionHandler
     */
    private function getConnectError($message, $code, $fromFunction)
    {
        if ((int)$code) {
            $this->throwDatabaseException(
                $message,
                $code,
                null,
                $fromFunction
            );
        }
    }

    /**
     * @param $message
     * @param $code
     * @param $previousException
     * @param $fromFunction
     * @throws ExceptionHandler
     */
    private function throwDatabaseException($message, $code, $previousException, $fromFunction)
    {
        throw new ExceptionHandler(
            $message,
            $code,
            $previousException,
            null,
            $fromFunction,
            $this
        );
    }

    /**
     * @param string $identifier
     * @return mixed|void
     * @throws ExceptionHandler
     */
    private function setLocalServerOptions($identifier)
    {
        $this->CONFIG->setServerOptions(
            [
                defined(MYSQLI_OPT_CONNECT_TIMEOUT) ?
                    MYSQLI_OPT_CONNECT_TIMEOUT : 0 => $this->CONFIG->getTimeout($identifier),
            ],
            $identifier
        );

        if (is_object($this->CONFIG->getConnection($identifier))) {
            foreach ($this->CONFIG->getServerOptions($identifier) as $optionKey => $optionValue) {
                /** @noinspection PhpParamsInspection */
                mysqli_options(
                    $this->CONFIG->getConnection($identifier),
                    $optionKey,
                    $optionValue
                );
            }
        }

        return true;
    }

    /**
     * @param $identifier
     * @return bool
     * @throws ExceptionHandler
     */
    private function connect_mysql($identifier)
    {
        // Special occasions.
        if (!Flag::getFlag('SQL_NEW_LINK')) {
            Flag::setFlag('SQL_NEW_LINK');
        }

        $connection = @mysql_connect(
            $this->getServerHost($identifier),
            $this->getServerUser($identifier),
            $this->getServerPassword($identifier),
            Flag::getFlag('SQL_NEW_LINK')
        );

        if (is_resource($connection)) {
            $this->CONFIG->setConnection(
                $connection,
                $identifier
            );
        }

        if (!$connection) {
            $errorMessage = empty(mysql_error()) ? sprintf(
                'Could not connect with deprecated driver to mysql server at %s.',
                $this->CONFIG->getServerHost()
            ) : mysql_error();
            $errorCode = !mysql_errno() ? Constants::LIB_DATABASE_CONNECTION_EXCEPTION : mysql_errno();
            $this->getConnectError($errorMessage, $errorCode, __FUNCTION__);
        } else {
            $this->getConnectError(mysql_error($connection), mysql_errno($connection), __FUNCTION__);
        }

        return is_resource($connection);
    }

    /**
     * @param $identifier
     * @return bool
     * @throws ExceptionHandler
     */
    private function connect_pdo($identifier)
    {
        $connection = null;
        $DSN = sprintf(
            'mysql:dbname=%s;host=%s',
            $this->CONFIG->getDatabase($identifier, false),
            $this->CONFIG->getServerHost($identifier)
        );

        $PDOException = null;
        try {
            $connection = new PDO(
                $DSN,
                $this->getServerUser($identifier),
                $this->getServerPassword($identifier),
                $this->getServerOptions($identifier)
            );
        } catch (PDOException $PDOException) {
            // Wait for it.
        }

        if (is_object($connection)) {
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->CONFIG->setConnection($connection, $identifier);
        } else {
            $this->getPdoError($connection, $PDOException);
        }

        return is_object($connection);
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
     * @param $connection
     * @param $PDOException
     * @throws ExceptionHandler
     */
    private function getPdoError($connection, $PDOException)
    {
        $errorMessage = sprintf(
            'Could not connect to PDO server at %s.',
            $this->CONFIG->getServerHost()
        );

        $errorCode = Constants::LIB_DATABASE_CONNECTION_EXCEPTION;

        if (method_exists($connection, 'errorInfo')) {
            $errorMessage = implode(' ', $connection->errorInfo());
        }
        if (get_class($PDOException) === 'PDOException') {
            $errorMessage = $PDOException->getMessage();
            if ((int)$validIntegerError = $PDOException->getCode()) {
                $errorCode = $validIntegerError;
            }
        }
        if ((int)$errorCode) {
            $this->throwDatabaseException(
                $errorMessage,
                $errorCode,
                $PDOException,
                __FUNCTION__
            );
        }
    }

    /**
     * @param int $preferredDriver
     * @param null $identifier
     * @return int
     * @throws ExceptionHandler
     */
    public function setPreferredDriver($preferredDriver = Drivers::DRIVER_MYSQL_IMPROVED, $identifier = null)
    {
        return $this->getInitializedDriver($preferredDriver, $identifier);
    }

    /**
     * @param $schemaName
     * @param $identifierName
     * @return $this|mixed
     * @throws ExceptionHandler
     * @noinspection PhpParamsInspection
     */
    public function setDatabase($schemaName, $identifierName = null)
    {
        $this->CONFIG->setDatabase($schemaName, $identifierName);

        if ($connection = $this->CONFIG->getConnection($identifierName)) {
            if ($this->getPreferredDriver($identifierName) === Drivers::DRIVER_MYSQL_IMPROVED) {
                mysqli_select_db($connection, $schemaName);
            } elseif ($this->getPreferredDriver($identifierName) === Drivers::DRIVER_MYSQL_DEPRECATED) {
                mysql_select_db($schemaName, $connection);
            } elseif ($this->getPreferredDriver($identifierName) === Drivers::DRIVER_MYSQL_PDO) {
                if (method_exists($connection, "select_db")) {
                    $connection->select_db($schemaName);
                } else {
                    $connection->query("use " . $schemaName);
                }
            }
        }

        return $this;
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
     * @param $userName
     * @param null $identifierName
     * @return DatabaseConfig
     */
    public function setServerUser($userName, $identifierName = null)
    {
        return $this->CONFIG->setServerUser($userName, $identifierName);
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
