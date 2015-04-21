<?php

final class Connection extends SQLModel
{
	public function connect($post) {
		// traitement connect
		$existUser = $this->bdd->prepare('SELECT COUNT(id)
		FROM users
		WHERE pseudo=:pseudo AND password=SHA1(:password)')->execute($post)->fetchSingle();

		if ($existUser) {
		$user = $this->bdd->prepare('SELECT
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

	public function disconnect() {
		session_destroy();
		return true;
	}
}
