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
		/***************************************
		Récupération des informations nécéssaires pour l'affichage de la page.
		***************************************/
		$forum = new Forum();

		$categories = $forum->getCategories();
		$topics = $forum->getTopics();
		$stats = $forum->getStats();

		/***************************************
		Récupération des nouveaux sujets/messages.
		***************************************/
		foreach ($topics as $i => $topic) {
			if ($this->connect) {
				$topics[$i]['view'] = (bool)$forum->userRequestViewTopic($this->session['id'], $topic['id']);
			}
			else {
				$topics[$i]['view'] = true;
			}
		}

		/***************************************
					Affichage de la page
		***************************************/

		$title = 'forum';
		$content = 'accueil.phtml';

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
		$forum = new Forum();

		if ($this->connect) {
			$title = 'Membres';
			$content = 'membres.phtml';

			$membres = $forum->getMembres();
			
			include __ROOT_DIR__ . '/views/index.phtml';
		}
		else {
			$this->session['informationUser'][] = 'Vous devez être connecté pour accéder à cette page.';
			$this->accueilAction();
		}
	}

	public function disconnectAction()
	{
		$connection = new Connection();

		$title = 'Disconnect';
		$content = 'disconnect.phtml';
		$connection->disconnect();

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
		$connection = new Connection();

		$connection->connect($this->post);

		$this->session = array_merge($this->session, $_SESSION);

		if (isset($this->session['pseudo'])) {
			$this->connect = true;
			$this->session['informationUser'][] = 'Connexion reussi. Bienvenue '. $this->session['pseudo'] . '.';
		}
		else {
			$this->connect = false;
			$this->session['informationUser'][] = 'Echec connection.';
		}

		$this->accueilAction();
	}

	public function handlingInscriptionAction()
	{
		$inscription = new Inscription();
		// On test si le formulaire est complet !
		$errorMessages = $inscription->formulaireCompleted($this->post);

		if (count($errorMessages) > 0) {
			// Il y'a des erreurs !
			$this->session['informationUser'] = $errorMessages;
			$this->inscriptionAction();
		}
		else {
			// On test si l'inscription est possible, pas de doublon présent.
			$errorMessages = $inscription->inscriptionFactory($this->post);

			if (count($errorMessages) > 0) {
				// Il y a des doublons :(
				$this->session['informationUser'] = $errorMessages;
				$this->inscriptionAction();
			}
			else {
				// On enregistre notre nouveau client/utilisateur.

				$inscription->createUser($this->post);
				$this->session['informationUser'][] = 'Bravo vous voila inscrit, vous pouvez vous connecter dès à présent.';
				$this->accueilAction();
			}
		}
	}

	public function topicAction()
	{
		$forum = new Forum();

		if ($this->connect) {

			/***************************************
				On enregistre le fait que l'utilisateur à vu le topic
			***************************************/
			$forum->userViewTopic($this->session['id'], $this->get['topicId']);

			/***************************************
					Affichage de la page
			***************************************/
			$title = 'Topic';
			$content = 'topic.phtml';

			$topic = $forum->getTopic($this->get['topicId']);
			$messages = $forum->getMessagesTopic($this->get['topicId']);

			include __ROOT_DIR__ . '/views/index.phtml';
		}
		else {
			$this->session['informationUser'][] = 'Vous devez être connecté pour accéder à cette page.';
			$this->accueilAction();
		}
	}

	public function messageReplyAction()
	{
		$forum = new Forum();

		if ($this->connect) {

			/********************************************
			Méthodes de réponse aux topics
			********************************************/
			$topic = $forum->getTopic($this->get['topicId']);
			$messages = $forum->getMessagesTopic($this->get['topicId']);

			// Test du message
			if ($forum->testReply($this->post)) {
				
				// On enregistre le message
				if ($forum->createReply($this->post, $this->session['id'], $topic['id'])) {
					$this->session['informationUser'][] = 'Votre message à bien été envoyé.';
				}
				else {
					$this->session['informationUser'][] = 'Vous ne pouvez poster qu\'une seule fois votre message.';
				}
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
		$forum = new Forum();

		if ($this->connect) {

			if ($forum->testTopic($this->post)) {
				
				// On enregistre le sujet
				if ($forum->createTopic($this->post, $this->session['id'], $this->get['categorieId'])) {
					$this->session['informationUser'][] = 'Votre sujet à bien été envoyé.';
				}
				else {
					$this->session['informationUser'][] = 'Vous ne pouvez poster qu\'une seule fois votre sujet.';
				}

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
		$forum = new Forum();

		if ($this->connect) {

			$forum->deleteMessage($this->get['messageId']);

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
		$forum = new Forum();

		if ($this->connect) {
			if (empty($this->file['avatar'])) {

				if (($avatar = $forum->uploadAvatar($this->files['avatar'], $this->session['id'])) !== false) {

					$this->session['informationUser'][] = 'Avatar modifié.';
					$this->session['avatar'] = $avatar;
				
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
