<?php

namespace TorneLIB;

require_once __DIR__ . "/tornevall_database_interface.php";

/**
 * Class libdriver_database_template Default interface of what drivers needs to be built
 * @package TorneLIB
 */
class libdriver_database_template implements libdriver_database_interface
{

    /** @var Identifier name */
    private $serverIdentifier;
    /** @var Options in */
    private $serverOptions;
    /** @var Hostname or address */
    private $serverHostAddr;
    /** @var Server username */
    private $serverUsername;
    /** @var Server password */
    private $serverPassword;
    /** @var int Server port name, defaults to mysql */
    private $serverPort = 3306;
    /** @var Predefined datbase name to connect to */
    private $serverDatabaseName;

    function __construct(
        $serverIdentifier = '',
        $serverOptions = array(),
        $serverHostAddr = null,
        $serverUsername = null,
        $serverPassword = null
    ) {
        parent::__construct($serverIdentifier, $serverOptions, $serverHostAddr, $serverUsername, $serverPassword);
    }

    /*****
     * INTERFACE SETUP BEGIN
     */

    /**
     * Identify current server with name
     *
     * @param string $serverIdentifier
     */
    public function setServerIdentifier($serverIdentifier = '')
    {
        $this->serverIdentifier = !empty($serverIdentifier) ? $serverIdentifier : "default";
    }

    /**
     * Get server name (identification)
     * @return Identifier
     */
    public function getServerIdentifier()
    {
        return $this->serverIdentifier;
    }

    /**
     * Set special options for database
     *
     * @param array $serverOptions
     */
    public function setServerOptions($serverOptions = array())
    {
        if (is_array($serverOptions) && count($serverOptions)) {
            $this->serverOptions = $serverOptions;
        }
    }

    /**
     * Get currrent set server options
     * @return Options
     */
    public function getServerOptions()
    {
        return $this->serverOptions;
    }

    /**
     * Set up host/addr to database server
     *
     * @param string $serverHostAddr
     */
    public function setServerHostAddr($serverHostAddr = '')
    {
        if (!empty($serverHostAddr)) {
            $this->serverHostAddr = $serverHostAddr;
        }
    }

    /**
     * Get current set host/addr to database server
     * @return Hostname
     */
    public function getServerHostAddr()
    {
        return $this->serverHostAddr;
    }

    /**
     * Set username credentials
     *
     * @param string $serverUsername
     */
    public function setServerUserName($serverUsername = '')
    {
        if (!empty($serverUsername)) {
            $this->serverUsername = $serverUsername;
        }
    }

    /**
     * Get current username credentials
     * @return Server
     */
    public function getServerUserName()
    {
        return $this->serverUsername;
    }

    /**
     * Set current password credentials
     *
     * @param string $serverPassword
     */
    public function setServerPassword($serverPassword = '')
    {
        if (!empty($serverPassword)) {
            $this->serverPassword = $serverPassword;
        }
    }

    /**
     * Get current password credentials
     * @return Server
     */
    public function getServerPassword()
    {
        return $this->serverPassword;
    }

    /**
     * Change default connector port
     *
     * @param int $serverPortNumber
     */
    public function setPort($serverPortNumber = 3306)
    {
        if (!empty($serverPortNumber) && is_numeric($serverPortNumber)) {
            $this->serverPort = $serverPortNumber;
        }
    }

    /**
     * Get the default connector port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->serverPort;
    }

    /**
     * Preconfigure database to connect to
     *
     * @param string $databaseName
     */
    public function setDatabase($databaseName = '')
    {
        if (!empty($databaseName)) {
            $this->serverDatabaseName = $databaseName;
        }
    }

    /**
     * Get preconfigured database to connect to
     *
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->serverDatabaseName;
    }

    /*
     * INTERFACE SETUP END
     *****/

    /*****
     * INTERFACE FUNCTIONS DEPENDENT ON DRIVER
     */

    /**
     * Connect to server with default set up or own data
     *
     * @param string $serverIdentifier
     * @param array $serverOptions
     * @param null $serverHostAddr
     * @param null $serverUsername
     * @param null $serverPassword
     */
    public function connect(
        $serverIdentifier = '',
        $serverOptions = array(),
        $serverHostAddr = null,
        $serverUsername = null,
        $serverPassword = null
    ) {
        $this->setServerIdentifier($serverIdentifier);
        $this->setServerOptions($serverOptions);
        $this->setServerHostAddr($serverHostAddr);
        $this->setServerUserName($serverUsername);
        $this->setServerPassword($serverPassword);
    }

    public function db($databaseName = '')
    {
        // TODO: Implement db() method.
    }

    public function query_raw($queryString = '')
    {
        // TODO: Implement query_raw() method.
    }

    public function query($queryString = '', $parameters = array())
    {
        // TODO: Implement query() method.
    }

    public function query_first($queryString = '', $parameters = array())
    {
        // TODO: Implement query_first() method.
    }

    public function query_prepare_first($queryString = '', $parameters = array())
    {
        // TODO: Implement query_prepare_first() method.
    }

    public function query_prepare($queryString = '', $parameters = array(), $tests = array())
    {
        // TODO: Implement query_prepare() method.
    }

    public function fetch($resource = null, $columnArray = true)
    {
        // TODO: Implement fetch() method.
    }

    public function escape($injectionString = null)
    {
        // TODO: Implement escape() method.
    }

    public function getLastInsertId()
    {
        // TODO: Implement getLastInsertId() method.
    }

}