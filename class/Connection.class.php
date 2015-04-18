<?php

abstract class Connection
{
	static function connect($post) {
		// traitement connect
		$bdd = new MySQL();
		$existUser = $bdd->prepare('SELECT COUNT(id) 
		FROM users 
		WHERE pseudo=:pseudo AND password=SHA1(:password)')->execute($post)->fetchSingle();

		if ($existUser) {
		$user = $bdd->prepare('SELECT
		id, pseudo, email, avatar, rights 
		FROM users 
		WHERE pseudo=:pseudo AND password=SHA1(:password)')->execute($post)->fetch();

			foreach ($user as $key => $value) {
				$_SESSION[$key] = $value;
			}

			return true;
		}
		else {
			return false;
		}
	}

	static function disconnect() {
		session_destroy();
		return true;
	}
}