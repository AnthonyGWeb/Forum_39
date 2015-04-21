<?php
final class Inscription extends SQLModel
{
	public function formulaireCompleted($post)
	{
		$errorMessages = array();

		if (strlen($post['pseudo']) < 4) {
			$errorMessages[] = 'Veuillez remplir votre pseudo ! (4 Caractères minimum)'; 
		}

		if (strlen($post['email']) < 4) {
			$errorMessages[] = 'Veuillez remplir votre email !'; 
		}

		if (strlen($post['password']) < 4) {
			$errorMessages[] = 'Veuillez remplir votre mot de passe ! (4 Caractères minimum)'; 
		}

		if ($post['password'] !== $post['password_confirm']) {
			$errorMessages[] = 'Les mots de passe ne correspondent pas !'; 
		}

		return $errorMessages;
	}

	public function inscriptionFactory($post)
	{
		$errorMessages = array();
		$this->bdd = new MySQL();
		
		if ($this->bdd->prepare('SELECT COUNT(id) FROM users WHERE pseudo=:pseudo')->execute(array('pseudo' => $post['pseudo']))->fetchSingle() != 0)	{

			$errorMessages[] = 'Le pseudo est déjà pris. Veuillez en choisir un autre'; 
		}

		if ($this->bdd->prepare('SELECT COUNT(id) FROM users WHERE email=:email')->execute(array('email' => $post['email']))->fetchSingle() != 0)	{

			$errorMessages[] = 'L\'email existe déjà. Essayer de vous connecter.'; 
		}

		return $errorMessages;
	}

	public function createUser($post)
	{
		$this->bdd = new MySQL();
		$this->bdd->prepare('INSERT INTO users (pseudo, email, password) VALUES (:pseudo, :email, SHA1(:password))')->execute(array( 
			'pseudo' => $post['pseudo'],
			'email' => $post['email'],
			'password' => $post['password'],
			));
	}
}