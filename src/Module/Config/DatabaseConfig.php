<?php

namespace TorneLIB\Module\Config;

use TorneLIB\Exception\Constants;
use TorneLIB\Exception\ExceptionHandler;

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
     */
    private $identifier = 'default';

    /**
     * @var array $identifiers Collection of added identifiers.
     */
    private $identifiers = [];

    /**
     * @var array $serverPort
     */
    private $serverPort = [];

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
     * @return array
     * @since 6.1.0
     */
    public function getServerPort($identifier = null)
    {
        return $this->serverPort[$this->getCurrentIdentifier($identifier)];
    }

    /**
     * @param array $serverPort
     * @param null $identifier
     * @since 6.1.0
     */
    public function setServerPort($serverPort, $identifier = null)
    {
        $this->serverPort[$this->getCurrentIdentifier($identifier)] = $serverPort;
    }

}
