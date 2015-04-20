<?php
class Controller
{
	private $post,
			$get,
			$session,
			$server,
			$files;
	private $connect;

	public function __construct(array $post, array $get, array $session, array $files, array $server)
	{
		$this->post = $post;
		$this->get = $get;
		$this->session = $session;
		$this->files = $files;
		$this->server = $server;

		$this->connect = (isset($this->session['pseudo'])) ? true : false;
	}

	public function __destruct() {
		$_SESSION = array_merge($_SESSION, $this->session);
	}

	public function accueilAction()
	{
		$title = 'Accueil';
		$content = 'accueil.phtml';

		$categories = Forum::getCategories();
		$topics = Forum::getTopics();
		$stats = Forum::getStats();

		include __ROOT_DIR__ . '/views/index.phtml';
	}

	public function inscriptionAction()
	{
		if (!$this->connect) {
			$title = 'Inscription';
			$content = 'inscription.phtml';
			include __ROOT_DIR__ . '/views/index.phtml';
		}
		else {
			$this->session['informationUser'][] = 'Vous êtes déja inscrit ...';
			$this->accueilAction();
		}
	}

	public function membresAction()
	{
		if ($this->connect) {
			$title = 'Membres';
			$content = 'membres.phtml';

			$membres = Forum::getMembres();
			
			include __ROOT_DIR__ . '/views/index.phtml';
		}
		else {
			$this->session['informationUser'][] = 'Vous devez être connecté pour accéder à cette page.';
			$this->accueilAction();
		}
	}

	public function disconnectAction()
	{
		$title = 'Disconnect';
		$content = 'disconnect.phtml';
		Connection::disconnect();

		$this->session = array();
		$this->connect = (isset($this->session['pseudo'])) ? true : false;

		$this->session['informationUser'][] = 'Vous êtes bien déconnecté.';

		$this->accueilAction();
	}

	public function profileAction()
	{
		if ($this->connect) {
			$title = 'Mon profil';
			$content = 'profile.phtml';
			include __ROOT_DIR__ . '/views/index.phtml';
		}
		else {
			$this->session['informationUser'][] = 'Vous devez être connecté pour accéder à cette page.';
			$this->accueilAction();
		}
	}

	public function handlingConnectAction()
	{
		Connection::connect($this->post);

		$this->session = array_merge($this->session, $_SESSION);

		if (isset($this->session['pseudo'])) {
			$this->connect = true;
			$this->session['informationUser'][] = 'Connection reussi. Bienvenue '. $this->session['pseudo'] . '.';
		}
		else {
			$this->connect = false;
			$this->session['informationUser'][] = 'Echec connection.';
		}

		$this->accueilAction();
	}

	public function handlingInscriptionAction()
	{
		// On test si le formulaire est complet !
		$errorMessages = Inscription::formulaireCompleted($this->post);

		if (count($errorMessages) > 0) {
			// Il y'a des erreurs !
			$this->session['informationUser'] = $errorMessages;
			$this->inscriptionAction();
		}
		else {
			// On test si l'inscription est possible, pas de doublon présent.
			$errorMessages = Inscription::inscriptionFactory($this->post);

			if (count($errorMessages) > 0) {
				// Il y a des doublons :(
				$this->session['informationUser'] = $errorMessages;
				$this->inscriptionAction();
			}
			else {
				// On enregistre notre nouveau client/utilisateur.

				Inscription::createUser($this->post);
				$this->session['informationUser'][] = 'Bravo vous voila inscrit, vous pouvez vous connecter dès à présent.';
				$this->accueilAction();
			}
		}
	}

	public function topicAction()
	{
		if ($this->connect) {
			$title = 'Topic';
			$content = 'topic.phtml';
			$topic = Forum::getTopic($this->get['topicId']);
			$messages = Forum::getMessagesTopic($this->get['topicId']);
			include __ROOT_DIR__ . '/views/index.phtml';
		}
		else {
			$this->session['informationUser'][] = 'Vous devez être connecté pour accéder à cette page.';
			$this->accueilAction();
		}
	}

	public function messageReplyAction()
	{
		if ($this->connect) {
			$topic = Forum::getTopic($this->get['topicId']);
			$messages = Forum::getMessagesTopic($this->get['topicId']);

			// Test du message
			if (Forum::testReply($this->post)) {
				
				// On enregistre le message
				Forum::createReply($this->post, $this->session['id'], $topic['id']);

				$this->session['informationUser'][] = 'Votre message à bien été envoyé.';
			}
			else {
				$this->session['informationUser'][] = 'A quoi sert une réponse si celle ci est vide ?';
			}
			
			$this->topicAction();
		}
		else {
			$this->session['informationUser'][] = 'Vous devez être connecté pour accéder à cette page.';
			$this->accueilAction();
		}
	}

	public function newTopicAction()
	{
		if ($this->connect) {
			$title = 'Nouveau sujet';
			$content = 'createTopic.phtml';
			$catId = $this->get['categorieId'];
			include __ROOT_DIR__ . '/views/index.phtml';
		}
		else {
			$this->session['informationUser'][] = 'Vous devez être connecté pour accéder à cette page.';
			$this->accueilAction();
		}
	}

	public function handlingTopicAction()
	{
		if ($this->connect) {

			if (Forum::testTopic($this->post)) {
				
				// On enregistre le sujet
				Forum::createTopic($this->post, $this->session['id'], $this->get['categorieId']);

				$this->session['informationUser'][] = 'Votre sujet à bien été envoyé.';
				$this->accueilAction();
			}
			else {
				$this->session['informationUser'][] = 'Merci de remplir un titre ainsi qu\'un sujet';
				$this->newTopicAction();
			}
		}
		else {
			$this->session['informationUser'][] = 'Vous devez être connecté pour accéder à cette page.';
			$this->accueilAction();
		}
	}

	public function handlingDeleteMessageAction()
	{
		if ($this->connect) {

			Forum::deleteMessage($this->get['messageId']);

			$this->session['informationUser'][] = 'Message supprimé ...';
			$this->topicAction();
		}
		else {
			$this->session['informationUser'][] = 'Vous devez être connecté pour accéder à cette page.';
			$this->accueilAction();
		}
	}

	public function uploadAvatarAction()
	{
		if ($this->connect) {
			if (empty($this->file['avatar'])) {
				if (Forum::uploadAvatar($this->files)) {
					$this->session['informationUser'][] = 'Avatar modifié.';
				}
				else {
					$this->session['informationUser'][] = 'Erreur lors de l\'upload.';
				}
			}

			$this->profileAction();
		}
		else {
			$this->session['informationUser'][] = 'Vous devez être connecté pour accéder à cette page.';
			$this->accueilAction();
		}
	}
}
