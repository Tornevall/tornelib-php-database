# tornelib-php-database 6.1

The rewritten database driver for the tornelib-series.
Written to autoselect proper driver regardless of system content. 

## Testings

Test works best with a database installed. Installing it automatically is not offered yet. You could do something like this to prepare data if you need to run tests:

    CREATE USER 'tornelib'@'localhost' IDENTIFIED BY 'tornelib1337';
    GRANT ALL PRIVILEGES ON tornelib_tests.* TO tornelib@localhost;

    CREATE DATABASE tornelib_tests;
    USE tornelib_tests;
    DROP TABLE IF EXISTS `tests`;
    CREATE TABLE `tests` (
      `dataindex` int(11) NOT NULL AUTO_INCREMENT,
      `data` varchar(45) NOT NULL,
      PRIMARY KEY (`dataindex`,`data`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

## To-Do

Exceptions to transfer to the exception handler.

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
