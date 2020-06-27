<?php

namespace TorneLIB;

/**
 * Class TORNEVALL_DATABASE_TYPES
 * @package TorneLIB
 */
abstract class TORNEVALL_DATABASE_TYPES
{
    const NONE = 0;
    const MYSQL = 1;
    const SQLITE3 = 2;
    const PGSQL = 3;
    const ODBC = 4;
    const MSSQL = 5;
    const PDO = 6;
}

/**
 * Class TORNEVALL_DATABASE_DRIVERS
 * @package TorneLIB
 */
abstract class TORNEVALL_DATABASE_DRIVERS
{
    const DRIVER_TYPE_NONE = 0;
    const DRIVER_MYSQL_IMPROVED = 1;
    const DRIVER_MYSQL_PDO = 2;
    const DRIVER_MYSQL_DEPRECATED = 3;
}

abstract class DATABASE_PORTS
{
    const MYSQL = 3306;
}

abstract class TORNEVALL_DATABASE_EXCEPTIONS
{
    const DRIVER_TYPE_MYSQLD_NOT_EXIST = 5000;
    const DRIVER_TYPE_MYSQLI_NOT_EXIST = 5001;
    const DRIVER_TYPE_MYSQLP_NOT_EXIST = 5002;
    const DRIVER_TYPE_MYSQLP_CONNECT_UNKNOWN_ERROR = 5003;
    const DRIVER_TYPE_MYSQLP_CHANGEDB_ERROR = 5004;
    const DRIVER_CONFIGURATION_MISSING = 5005;
    const DRIVER_EMPTY_QUERY = 5006;
    const DRIVER_EMPTY_STATEMENT = 5007;
    const DRIVER_PREPARE_DEPRECATED = 5008;
    const DRIVER_TYPE_UNDEFINED = 5009;
}
