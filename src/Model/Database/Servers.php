<?php

namespace TorneLIB\Model\Database;

/**
 * Class Servers
 * @package TorneLIB\Model\Database
 */
class Servers
{
    /**
     * @var ServerData
     */
    private $servers = [];

    /**
     * @return array
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * @param array $serverArray
     * @return Servers
     * @noinspection PhpUnused
     */
    public function setServers($serverArray = [])
    {
        foreach ($serverArray as $serverKey => $serverVariables) {
            $serverData = new ServerData();
            foreach ($serverVariables as $key => $value) {
                $serverData->{sprintf('set%s', ucfirst($key))}($value);
            }
            $this->servers[$serverKey] = $serverData;
        }

        return $this;
    }

    /**
     * @param null $identifier
     * @return mixed|null
     */
    public function getServer($identifier = null)
    {
        if (is_null($identifier)) {
            $identifier = 'localhost';
        }
        return isset($this->servers[$identifier]) ? $this->servers[$identifier] : null;
    }
}
