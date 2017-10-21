<?php

namespace TorneLIB;

interface libdriver_interface {
	function __construct( $serverIdentifier = '', $serverOptions = array(), $serverHostAddr = null, $serverUsername = null, $serverPassword = null );

	public function setServerIdentifier( $serverIdentifier = '' );

	public function getServerIdentifier();

	public function setServerOptions( $serverOptions = array() );

	public function getServerOptions();

	public function setServerHostAddr( $serverHostAddr = '' );

	public function getServerHostAddr();

	public function setServerUserName( $serverUsername = '' );

	public function getServerUserName();

	public function setServerPassword( $serverPassword = '' );

	public function getServerPassword();

	public function setPort( $serverPortNumber = 3306 );

	public function getPort();

	public function setDatabase( $databaseName = '' );

	public function getDatabase();

	public function connect( $serverIdentifier = '', $serverOptions = array(), $serverHostAddr = null, $serverUsername = null, $serverPassword = null );

	public function db( $databaseName = '' );

	public function getLastInsertId();

	public function query_raw($queryString = '');

	public function query($queryString = '', $parameters = array());

	public function query_first($queryString = '', $parameters = array());

	public function query_prepare_first($queryString = '', $parameters = array());

	public function query_prepare( $queryString = '', $parameters = array(), $tests = array() );

	public function fetch( $resource = null, $columnArray = true );

	public function escape( $injectionString = null );
}