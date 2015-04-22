<?php
final class Forum extends SQLModel
{
	public function getCategories()
	{
		$categories = $this->bdd->prepare('SELECT * FROM categories')->execute()->fetchAll();

		return $categories;
	}

	public function getTopics()
	{
		$topics = $this->bdd->prepare('SELECT
		topics.id,
		topics.titre,
		topics.categorie_id,
		topics.date_create AS topic_date_create,
		users.pseudo AS user_pseudo
		FROM topics
		LEFT JOIN users ON topics.user_id = users.id')->execute()->fetchAll();

		return $topics;
	}

	public function getTopic($id)
	{
		$topic = $this->bdd->prepare('SELECT
			topics.id,
			topics.message,
			topics.titre,
			topics.user_id,
			topics.date_create AS topics_date_create,
			users.pseudo,
			users.rights,
			users.avatar
			FROM topics
			LEFT JOIN users ON topics.user_id=users.id
			WHERE topics.id=:topic')->execute(array('topic' => $id,))->fetch();

		return $topic;
	}

	public function getMessagesTopic($topicId)
	{
		$messages = $this->bdd->prepare('SELECT
			messages.id AS message_id,
			messages.msg,
			messages.date_create,
			messages.user_id,
			users.rights,
			users.pseudo,
			users.avatar
			FROM messages
			LEFT JOIN users ON messages.user_id=users.id
			WHERE messages.topic_id=:topic')->execute(array('topic' => $topicId,))->fetchAll();

		return $messages;
	}

	public function testReply($post)
	{
		return (empty($post['reply'])) ? false : true;
	}

	public function createReply($post, $user_id, $topic_id)
	{
		/***********************************************
			On verifie que l'utilisateur ne renvoie pas les même datas !!!! F5 alt+r etc...
		************************************************/
		$postExist = $this->bdd->prepare('SELECT COUNT(id)
		FROM messages
		WHERE msg = :msg')
		->execute(array(
			'msg' => $post['reply'],
		))
		->fetchSingle();

		if ($postExist == 0) {
			/***********************************************
				On enregistre les infos ! :)
			************************************************/
			$this->bdd->prepare('INSERT INTO messages
			(msg, user_id, topic_id, date_create)
			VALUES
			(:msg, :user_id, :topic_id, NOW())')
			->execute(array(
				'msg' => $post['reply'],
				'user_id' => $user_id,
				'topic_id' => $topic_id,
			));

			return true;
		}

		return false;
	}

	public function testTopic($post)
	{
		return (empty($post['titre']) || empty($post['message'])) ? false : true;
	}

	public function createTopic($post, $user_id, $categorie_id)
	{
		/***********************************************
			On verifie que l'utilisateur ne renvoie pas les même datas !!!! F5 alt+r etc...
		************************************************/
		$topicExist = $this->bdd->prepare('SELECT COUNT(id)
		FROM topics
		WHERE titre = :titre AND message = :message')
		->execute(array(
			'titre' => $post['titre'],
			'message' => $post['message'],
		))
		->fetchSingle();

		if ($topicExist == 0) {
			/***********************************************
				On enregistre les infos ! :)
			************************************************/
			$this->bdd->prepare('INSERT INTO topics
			(titre, message, user_id, categorie_id, date_create)
			VALUES
			(:titre, :message, :user_id, :categorie_id, NOW())')
			->execute(array(
				'titre' => $post['titre'],
				'message' => $post['message'],
				'user_id' => $user_id,
				'categorie_id' => $categorie_id,
			));

			return true;
		}

		return false;
	}

	public function getStats()
	{
		$counts = array();
		/******************************************
					COUNT TOPIC AND MESSAGE
		*******************************************/
		$tmp = $this->bdd->prepare('SELECT 
		categories.id AS categorie_id, 
		COUNT(DISTINCT topics.id) AS nbr_topic, 
		COUNT(DISTINCT messages.id) + COUNT(DISTINCT topics.id) AS nbr_message
		FROM categories
		LEFT JOIN topics ON topics.categorie_id = categories.id
		LEFT JOIN messages ON messages.topic_id = topics.id
		GROUP BY categories.id
		')->execute()->fetchAll();

		$counts['numberTopic'] = array();

		foreach ($tmp as $val) {
			$counts['numberTopic'][$val['categorie_id']]['nbr_topic'] = $val['nbr_topic'];
			$counts['numberTopic'][$val['categorie_id']]['nbr_message'] = $val['nbr_message'];
		}

		/*******************************************
				COUNT MESSAGES par TOPIC
		*******************************************/

		$tmp = $this->bdd->prepare('SELECT
		COUNT(messages.id) AS nbr,
		topics.id AS categorie_id
		FROM topics
		LEFT JOIN messages ON topics.id = messages.topic_id
		GROUP BY topics.id')->execute()->fetchAll();

		$counts['numberMessage'] = array();

		foreach ($tmp as $val) {
			$counts['numberMessage'][$val['categorie_id']] = $val['nbr'] + 1;
		}

		return $counts;
	}

	public function deleteMessage($messageId)
	{
		$this->bdd->prepare('DELETE FROM messages WHERE id=:id')
		->execute(array(
			'id' => $messageId,
		));

		return true;
	}

	public function getMembres()
	{
		$membres = $this->bdd->prepare('SELECT
		pseudo,
		email,
		avatar,
		rights
		FROM users')
		->execute()->fetchAll();

		return $membres;
	}

	public function uploadAvatar($files, $id)
	{
		if ($files['size'] < 2000000) {

			$nameRandom = $files['name'] . time();
			$arrayTypes= [
				'image/jpeg' => '.jpeg',
				'image/jpg' => '.jpg',
				'image/png' => '.png',
			];

			if (array_key_exists($files['type'], $arrayTypes)) {

				$tmp = $files['tmp_name'];
				$img = sha1($nameRandom) . $arrayTypes[$files['type']];
				$folderTarget = 'img/' . $img;


				if (move_uploaded_file($tmp, $folderTarget)) {
					$this->bdd = new MySQL();
					$this->bdd->prepare('UPDATE users
						SET avatar=:avatar
						WHERE id=:id')
					->execute(array(
						'id' => $id,
						'avatar' => $img,
					));

					return $img;
				}
			}
		}

		return false;
	}

	public function userViewTopic($id, $topicId)
	{
		/*****************************************
			Si l'utilisateur n a pas déja vu le topic on l'enregistre
		*****************************************/
		if (!Forum::userRequestViewTopic($id, $topicId)) {

			$this->bdd->prepare('INSERT INTO user_topic
			(user_id, topic_id)
			VALUES (:user_id, :topic_id)')
			->execute(array(
				'user_id' => $id,
				'topic_id' => $topicId,
			));
		}

		/*****************************************
			Si l'utilisateur n a pas déja vu les messages on l'enregistre également
		*****************************************/
		$messages = Forum::getMessagesTopic($topicId);

		foreach ($messages as $message) {
			if (!Forum::userRequestViewMessage($id, $message['message_id'])) {
				$this->bdd->prepare('INSERT INTO user_message
				(user_id, message_id)
				VALUES (:user_id, :message_id)')
				->execute(array(
					'user_id' => $id,
					'message_id' => $message['message_id'],
				));
			}
		}

		return true;
	}

	public function userRequestViewTopic($id, $topicId)
	{
		/*********************************************
		Renvoi true ou false si il y a de nouveau message non lu.
		*********************************************/

		// Test des topics
		$topicView = (bool) $this->bdd->prepare('SELECT IF(COUNT(user_id) > 0, "1", "0")
		FROM user_topic
		WHERE user_id = :user AND topic_id = :topic')
		->execute(array(
			'user' => $id,
			'topic' => $topicId,
		))->fetchSingle();

		// Test des messages
		$messages = Forum::getMessagesTopic($topicId);
		$testViewMessage = array();
		$resultMessage = true; // si y a pas de message on test seulement le topic.

		foreach ($messages as $value) {
			if (Forum::userRequestViewMessage($id, $value['message_id'])) {
				$testViewMessage[] = true;
			}
			else {
				$testViewMessage[] = false;
			}
		}

		$resultMessage = in_array(false, $testViewMessage);

		return ($topicView && !$resultMessage) ? true : false;
		//return false;
	}

	public function userRequestViewMessage($id, $messageId)
	{
		/********************************************
		Renvoi true ou false si l'utilisateur à vu le message
		*********************************************/
		return (bool) $this->bdd->prepare('SELECT IF(COUNT(user_id) > 0, "1", "0")
		FROM user_message
		WHERE user_id = :user AND message_id = :message')
		->execute(array(
			'user' => $id,
			'message' => $messageId,
		))->fetchSingle();
	}
}
