<?php

/*
function routing($page)
{
	$routes = include __ROOT_DIR__ . '/config/routing.php';

	//return array_key_exists($page, $routes) ? $routes[$page]['method'] :

	if (array_key_exists($page, $routes)) {

		/***********************************************
			On list tous les $get 
		***********************************************/
		/*foreach ($get as $key => $value) {

			// On test si ils sont NOT null ou = Page 
			if (isset($routes[$get['page']]['get'][$key])) {
				if(($routes[$get['page']]['get'][$key] === 'is_numeric' && is_numeric($value)) ||  $routes[$get['page']]['get'][$key] === 'string') {
				echo $value .'<br>';
				var_dump($routes[$get['page']]['get']);
				//echo $routes[$get['page']][$key] . '<br>';
				} else {
					var_dump($value);
					die('blahblah');
				}
			} else if($key === 'page') {
				continue;
			} else {
				die('Valeurs incorrect !!');
			}
		}

		return $routes[$page]['method'];
	} else {

		//http_response_code(404);
		return $routes['accueil']['method'];
		//die('Page inaccessible');
	}
}
*/

/**********************************************************/

final class Routing
{
	private $routes;

	public function __construct()
	{
		$this->routes = include __ROOT_DIR__ . '/config/routing.php';
	}

	public function secure($get, $post)
	{
		if (isset($get['page']) && array_key_exists($get['page'], $this->routes)) {
			/**************************************
			Securisation des parametres GET et POST
			**************************************/
			$this->secureGET($get);
			$this->securePOST($post, $get);

			return $this->routes[$get['page']]['method'];
		} else {
			return $this->routes['accueil']['method'];
		}
	}

	public function secureGET($get)
	{
		foreach ($get as $key => $value) {

			// On test si ils sont NOT null ou = Page 
			if (isset($this->routes[$get['page']]['get'][$key])) {

				if((!$this->routes[$get['page']]['get'][$key] === 'is_numeric' || !is_numeric($value)) &&  $this->routes[$get['page']]['get'][$key] !== 'string') {

					die('Valeurs incorrect !!');
				}
			} else if($key === 'page') {
				continue;
			} else {
				die('Valeurs incorrect !!');
			}
		}
	}

	public function securePOST($post, $get)
	{
		/*foreach ($post as $key => $value) {
			// On test si ils sont NOT null ou = Page 
			if (isset($this->routes[$get['page']]['post'][$key])) {

				if((!$this->routes[$get['page']]['post'][$key] === 'is_numeric' || !is_numeric($value)) &&  $this->routes[$post['page']]['get'][$key] !== 'string') {

					die('Valeurs incorrect !!');
				}
			} else {
				die('Valeurs incorrect !!');
			}
		}*/
	}
}
