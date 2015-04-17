<?php
require_once __ROOT_DIR__ . '/controller.php';
require_once __ROOT_DIR__ . '/routing.php';

require_once __ROOT_DIR__ . '/class/Forum.class.php';
require_once __ROOT_DIR__ . '/class/Inscription.class.php';
require_once __ROOT_DIR__ . '/class/Connection.class.php';
require_once __ROOT_DIR__ . '/sql/MySQL.class.php';



session_start();

$ctrl = new Controller($_POST, $_GET, $_SESSION, $_SERVER);
$page = (isset($_GET['page'])) ? $_GET['page'] : null;
$route = routing($page);
$ctrl->{$route}();

unset($route, $ctrl);
die;
