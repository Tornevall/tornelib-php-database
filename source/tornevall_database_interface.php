<?php

namespace TorneLIB;

interface libdriver_interface {
	public function Connect();
	public function Db();

	public function getLastInsertId();

	public function Query_Raw();
	public function Query();
	public function Query_First();
	public function Query_Prepare_First();
	public function Query_Prepare();
	public function Fetch();

	public function Escape();
}