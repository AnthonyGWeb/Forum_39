<?php
abstract class SQLModel
{
	private $bdd;

	public function __construct()
	{
		$this->bdd = new MySQL();
	}

	public function __destruct()
	{
		// Fermeture de la bdd
	}
}