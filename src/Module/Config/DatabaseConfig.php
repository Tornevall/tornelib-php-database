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
    private $identifier = 'localhost';

    /**
     * @var array $identifiers Collection of added identifiers.
     */
    private $identifiers = [];

    /**
     * Get name of chosen database for connection ("use schema").
     *
     * @param string $identifier
     * @return string
     * @throws ExceptionHandler
     * @since 6.1.0
     */
    public function getDatabase($identifier = '')
    {
        if (isset($this->database[$identifier]) && !empty($this->database[$identifier])) {
            $return = $this->database[$identifier];
        } elseif (isset($this->database[$this->identifier]) && !empty($this->database[$this->identifier])) {
            $return = $this->database[$this->identifier];
        } else {
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
    public function setDatabase($database, $identifier = '')
    {
        if ($identifier) {
            $this->database[$identifier] = $database;
        } else {
            $this->database[$this->identifier] = $database;
        }

        return $this;
    }

    /**
     * Returns current identifier even if there may be more identifiers added.
     *
     * @return string
     * @since 6.1.0
     */
    public function getIdentifier()
    {
        return $this->identifier;
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
}
