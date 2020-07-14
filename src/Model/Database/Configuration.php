<?php

namespace TorneLIB\Model\Database;

class Configuration
{
    /**
     * @var Servers
     */
    public $database;

    /**
     * @param Servers $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    public function __call($name, $arguments)
    {
        $return = null;
        $variableName = lcfirst(substr($name, 3));

        if (isset($this->$variableName) && (0 === strpos($name, "get"))) {
            $return = $this->$variableName;
        }

        return $return;
    }
}
