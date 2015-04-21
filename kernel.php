<?php
require_once __ROOT_DIR__ . '/controller.php';
require_once __ROOT_DIR__ . '/routing.php';

require_once __ROOT_DIR__ . '/sql/class/SQLModel.class.php';
require_once __ROOT_DIR__ . '/sql/class/Forum.class.php';
require_once __ROOT_DIR__ . '/sql/class/Inscription.class.php';
require_once __ROOT_DIR__ . '/sql/class/Connection.class.php';
require_once __ROOT_DIR__ . '/sql/MySQL.class.php';

session_start();

$routing = new Routing();
$ctrl = new Controller($_POST, $_GET, $_SESSION,$_FILES , $_SERVER);

$route = $routing->secure($_GET, $_POST);
$ctrl->{$route}();

unset($route, $ctrl);
die;
