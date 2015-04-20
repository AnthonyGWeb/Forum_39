<?php
abstract class Forum
{
	static function getCategories()
	{
		$bdd = new MySQL();
		$categories = $bdd->prepare('SELECT * FROM categories')->execute()->fetchAll();

		return $categories;
	}

	static function getTopics()
	{
		$bdd = new MySQL();
		$topics = $bdd->prepare('SELECT * FROM topics')->execute()->fetchAll();

		return $topics;
	}

	static function getTopic($id)
	{
		$bdd = new MySQL();
		$topic = $bdd->prepare('SELECT 
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

	static function getMessagesTopic($topicId)
	{
		$bdd = new MySQL();
		$messages = $bdd->prepare('SELECT 
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

	static function testReply($post)
	{
		return (empty($post['reply'])) ? false : true;
	}

	static function createReply($post, $user_id, $topic_id)
	{
		$bdd = new MySQL();
		$bdd->prepare('INSERT INTO messages
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

	static function testTopic($post)
	{
		return (empty($post['titre']) || empty($post['message'])) ? false : true;
	}

	static function createTopic($post, $user_id, $categorie_id)
	{
		$bdd = new MySQL();
		$bdd->prepare('INSERT INTO topics
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

	static function getStats()
	{
		$bdd = new MySQL();
		$counts = array();

		/*******************************************
				COUNT TOPIC
		*******************************************/

		$tmp = $bdd->prepare('SELECT
		COUNT(topics.id) AS nbr, categories.id AS categorie_id
			FROM categories
			LEFT JOIN topics ON categories.id = topics.categorie_id
			GROUP BY categories.id')->execute()->fetchAll();
		
		$counts['numberTopic'] = array();

		foreach ($tmp as $val) {
			$counts['numberTopic'][$val['categorie_id']] = $val['nbr'];
		}

		/*******************************************
				COUNT MESSAGES
		*******************************************/

		$tmp = $bdd->prepare('SELECT
		COUNT(messages.id) AS nbr, topics.id AS categorie_id
			FROM topics
			LEFT JOIN messages ON topics.id = messages.topic_id
			GROUP BY topics.id')->execute()->fetchAll();
		
		$counts['numberMessage'] = array();

		foreach ($tmp as $val) {
			$counts['numberMessage'][$val['categorie_id']] = $val['nbr'] + 1;
		}

		return $counts;
	}

	static function deleteMessage($messageId)
	{
		$bdd = new MySQL();
		$bdd->prepare('DELETE FROM messages WHERE id=:id')
		->execute(array(
			'id' => $messageId,
		));

		return true;
	}

	static function getMembres()
	{
		$bdd = new MySQL();
		$membres = $bdd->prepare('SELECT
		pseudo,
		email,
		avatar,
		rights
		FROM users')
		->execute()->fetchAll();

		return $membres;
	}

	static function uploadAvatar($files)
	{
		echo 'bar';
		var_dump($files['avatar']);

		
		
		return true;
	}
}