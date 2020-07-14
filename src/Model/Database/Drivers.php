<?php

namespace TorneLIB\Model\Database;

class Drivers
{
    const MYSQL_IMPROVED = 1;
    const MYSQL_PDO = 2;
    const MYSQL_DEPRECATED = 3;

    /** @var int Unavailable method/driver, same as the error (LIB_DATABASE_DRIVER_UNAVAILABLE). */
    const DRIVER_OR_METHOD_UNAVAILABLE = 4004;
}
