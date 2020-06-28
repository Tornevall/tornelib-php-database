<?php

namespace TorneLIB\Module\Config;

use JsonMapper;
use JsonMapper_Exception;
use TorneLIB\Exception\Constants;
use TorneLIB\Exception\ExceptionHandler;
use TorneLIB\Model\Database\Configuration;
use TorneLIB\Model\Database\Servers;
use TorneLIB\Model\Database\Types;

/**
 * Class DatabaseConfig
 * @package TorneLIB\Module
 */
class DatabaseConfig
{
    /**
     * @var array $database Schema names.
     * @since 6.1.0
     */
    private $database = [];

    /**
     * @var string $identifier Current identifier. If none, this is always the localhost.
     * @since 6.1.0
     */
    private $identifier = 'default';

    /**
     * @var array $identifiers Collection of added identifiers.
     * @since 6.1.0
     */
    private $identifiers = [];

    /**
     * Always default to MySQL
     * @var array $serverPort
     * @since 6.1.0
     */
    private $serverPort = [
        'default' => 3306,
    ];

    /**
     * @var array $serverHost
     * @since 6.1.0
     */
    private $serverHost = [];

    /**
     * @var array $serverUser
     * @since 6.1.0
     */
    private $serverUser = [];

    /**
     * @var array
     * @since 6.1.0
     */
    private $serverPassword = [];

    /**
     * @var array
     * @since 6.1.0
     */
    private $serverType = [
        'default' => Types::MYSQL,
    ];

    /**
     * @var array
     * @since 6.1.0
     */
    private $serverOptions = [];

    /**
     * Get name of chosen database for connection ("use schema").
     *
     * @param string $identifier
     * @return string
     * @throws ExceptionHandler
     * @since 6.1.0
     */
    public function getDatabase($identifier = null)
    {
        $return = isset($this->database[$this->getCurrentIdentifier($identifier)]) ?
            $this->database[$this->getCurrentIdentifier($identifier)] : null;

        // Make sure the variable exists before using ikt.
        if (is_null($return)) {
            throw new ExceptionHandler(
                sprintf(
                    'Database is not set for connection "%s".',
                    !empty($identifier) ? $identifier : $this->identifier
                ),
                Constants::LIB_DATABASE_NOT_SET
            );
        }

        return (string)$return;
    }

    /**
     * @param string $database
     * @param string $identifier
     * @return DatabaseConfig
     * @since 6.1.0
     */
    public function setDatabase($database, $identifier = null)
    {
        $this->database[$this->getCurrentIdentifier($identifier)] = $database;

        return $this;
    }

    /**
     * @param null $identifier
     * @return string
     */
    private function getCurrentIdentifier($identifier = null)
    {
        $return = $this->getIdentifier();
        if (!empty($identifier) && !is_null($identifier)) {
            $return = $identifier;
        }
        return $return;
    }

    /**
     * Returns current identifier even if there may be more identifiers added.
     *
     * @return string
     * @since 6.1.0
     */
    public function getIdentifier()
    {
        return !empty($this->identifier) ? $this->identifier : 'default';
    }

    /**
     * Set "current" identifier.
     * @param string $identifier
     * @return DatabaseConfig
     * @since 6.1.0
     */
    public function setIdentifier($identifier)
    {
        if (!in_array($identifier, $this->identifiers, true)) {
            $this->identifiers[] = $identifier;
        }

        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return array
     * @since 6.1.0
     */
    public function getIdentifiers()
    {
        return $this->identifiers;
    }

    /**
     * @param null $identifier
     * @return null|string
     * @since 6.1.0
     */
    public function getServerPort($identifier = null)
    {
        return isset($this->serverPort[$this->getCurrentIdentifier($identifier)]) ?
            $this->serverPort[$this->getCurrentIdentifier($identifier)] : null;
    }

    /**
     * @param int $serverPort
     * @param null $identifier
     * @return DatabaseConfig
     * @since 6.1.0
     */
    public function setServerPort($serverPort, $identifier = null)
    {
        $this->serverPort[$this->getCurrentIdentifier($identifier)] = $serverPort;

        return $this;
    }

    /**
     * @param null $identifier
     * @return string
     * @since 6.1.0
     */
    public function getServerHost($identifier = null)
    {
        return isset($this->serverHost[$this->getCurrentIdentifier($identifier)]) ?
            $this->serverHost[$this->getCurrentIdentifier($identifier)] : '127.0.0.1';
    }

    /**
     * @param string $serverHost
     * @param null $identifier
     * @return DatabaseConfig
     * @since 6.1.0
     */
    public function setServerHost($serverHost, $identifier = null)
    {
        $this->serverHost[$this->getCurrentIdentifier($identifier)] = $serverHost;

        return $this;
    }

    /**
     * @param null $identifier
     * @return null|string
     * @since 6.1.0
     */
    public function getServerUser($identifier = null)
    {
        return isset($this->serverUser[$this->getCurrentIdentifier($identifier)]) ?
            $this->serverUser[$this->getCurrentIdentifier($identifier)] : null;
    }

    /**
     * @param array $serverUser
     * @param null $identifier
     * @return DatabaseConfig
     * @since 6.1.0
     */
    public function setServerUser($serverUser, $identifier = null)
    {
        $this->serverUser[$this->getCurrentIdentifier($identifier)] = $serverUser;

        return $this;
    }

    /**
     * @param null $identifier
     * @return null|string
     * @since 6.1.0
     */
    public function getServerPassword($identifier = null)
    {
        return isset($this->serverPassword[$this->getCurrentIdentifier($identifier)]) ?
            $this->serverPassword[$this->getCurrentIdentifier($identifier)] : null;
    }

    /**
     * @param $serverPassword
     * @param null $identifier
     * @return DatabaseConfig
     * @since 6.1.0
     */
    public function setServerPassword($serverPassword, $identifier = null)
    {
        $this->serverPassword[$this->getCurrentIdentifier($identifier)] = $serverPassword;

        return $this;
    }

    /**
     * @param null $identifier
     * @return null|string
     * @since 6.1.0
     */
    public function getServerType($identifier = null)
    {
        return isset($this->serverType[$this->getCurrentIdentifier($identifier)]) ?
            $this->serverType[$this->getCurrentIdentifier($identifier)] : null;
    }

    /**
     * @param int $serverType
     * @param null $identifier
     * @return DatabaseConfig
     * @since 6.1.0
     */
    public function setServerType($serverType = Types::MYSQL, $identifier = null)
    {
        $this->serverType[$this->getCurrentIdentifier($identifier)] = $serverType;

        return $this;
    }

    /**
     * @param null $identifier
     * @return array
     * @since 6.1.0
     */
    public function getServerOptions($identifier = null)
    {
        return isset($this->serverOptions[$this->getCurrentIdentifier($identifier)]) ?
            $this->serverOptions[$this->getCurrentIdentifier($identifier)] : [];
    }

    /**
     * @param $serverOptions
     * @param null $identifier
     * @return DatabaseConfig
     * @since 6.1.0
     */
    public function setServerOptions($serverOptions, $identifier = null)
    {
        $this->serverOptions[$this->getCurrentIdentifier($identifier)] = $serverOptions;

        return $this;
    }

    /**
     *
     * @param $jsonFile
     * @return mixed
     * @throws ExceptionHandler
     * @throws JsonMapper_Exception
     */
    public function getConfig($jsonFile)
    {
        $return = null;

        if (file_exists($jsonFile)) {
            $return = $this->getConfigByJson($jsonFile);
        } else {
            throw new ExceptionHandler(
                sprintf(
                    'Configuration file %s not found.',
                    $jsonFile
                ),
                404
            );
        }

        return $return;
    }

    /**
     * @param $jsonFile
     * @return Servers
     * @throws ExceptionHandler
     * @throws JsonMapper_Exception
     * @since 6.1.0
     */
    private function getConfigByJson($jsonFile)
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        $map = @json_decode(
            @file_get_contents($jsonFile),
            false
        );
        if (is_null($map)) {
            $this->throwNoConfig(__CLASS__, __FUNCTION__, $jsonFile);
        }

        return $this->getMappedJson($map);
    }

    /**
     * @param $class
     * @param $function
     * @param $jsonFile
     * @throws ExceptionHandler
     */
    private function throwNoConfig($class, $function, $jsonFile)
    {
        throw new ExceptionHandler(
            sprintf(
                'Function %s::%s called by file %s did not contain any configuration.',
                $class,
                $function,
                $jsonFile
            ),
            Constants::LIB_DATABASE_EMPTY_JSON_CONFIG
        );
    }

    /**
     * @param $mapFromJson
     * @return Servers
     * @throws ExceptionHandler
     * @throws JsonMapper_Exception
     * @since 6.1.0
     */
    private function getMappedJson($mapFromJson)
    {
        $json = (new JsonMapper())->map(
            $mapFromJson,
            new Configuration()
        );
        $this->throwWrongConfigClass($json);
        return $json->getDatabase();
    }

    /**
     * @param $json
     * @throws ExceptionHandler
     */
    private function throwWrongConfigClass($json)
    {
        if (get_class($json) !== Configuration::class) {
            throw new ExceptionHandler(
                sprintf(
                    '%s configuration class mismatch: %s, expected: %s.',
                    __CLASS__,
                    get_class($json),
                    Configuration::class
                ),
                Constants::LIB_DATABASE_EMPTY_JSON_CONFIG
            );
        }
    }
}
