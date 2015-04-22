<?php

return [
		'accueil' => [
			'method' => 'accueilAction',
			'get' => null,
			'post' => null,
		],

		'membres' => [
			'method' => 'membresAction',
			'get' => null,
			'post' => null,
		],

		'inscription' => [
			'method' => 'inscriptionAction',
			'get' => null,
			'post' => null,
		],

		'deconnection' => [
			'method' => 'disconnectAction',
			'get' => null,
			'post' => null,
		],

		/******************************************

		******************************************/

		'handlingConnection' => [
			'method' => 'handlingConnectAction',
			'get' => null,
			'post' => null,
		],

		'handlingInscription' => [
			'method' => 'handlingInscriptionAction',
			'get' => null,
			'post' => null,
		],

		'handlingTopic' => [
			'method' => 'handlingTopicAction',
			'get' => null,
			'post' => null,
		],

		'handlingDeleteMessage' => [
			'method' => 'handlingDeleteMessageAction',
			'get' => null,
			'post' => null,
		],

		/******************************************

		******************************************/

		'topic' => [
			'method' => 'topicAction',
			'get' => [
				'topicId' => 'is_numeric',
				],
			'post' => null,
		],

		'message_reply' => [
			'method' => 'messageReplyAction',
			'get' => [
				'topicId' => 'is_numeric',
				],
			'post' => null,
		],

		'new_topic' => [
			'method' => 'newTopicAction',
			'get' => [
				'categorieId' => 'is_numeric',
				],
			'post' => null,
		],

		'profile' => [
			'method' => 'profileAction',
			'get' => null,
			'post' => null,
		],

		'uploadAvatar' => [
			'method' => 'uploadAvatarAction',
			'get' => null,
			'post' => null,
		],
	];