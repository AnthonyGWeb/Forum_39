<?php
function routing($page)
{
	$routes = [
		'accueil' => 'accueilAction',
		'membres' => 'membresAction',
		'inscription' => 'inscriptionAction',
		'deconnection' => 'disconnectAction',

		'handlingConnection' => 'handlingConnectAction',
		'handlingInscription' => 'handlingInscriptionAction',
		'handlingTopic' => 'handlingTopicAction',
		'handlingDeleteMessage' => 'handlingDeleteMessageAction',

		'topic' => 'topicAction',
		'message_reply' => 'messageReplyAction',
		'new_topic' => 'newTopicAction',
		'profile' => 'profileAction',
		'uploadAvatar' => 'uploadAvatarAction',
	];

	return array_key_exists($page, $routes) ? $routes[$page] : $routes['accueil'];
}
