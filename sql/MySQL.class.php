<?php
final class MySQL extends PDO
{
    private $stmt;

	public function __construct()
	{
		$mysqlConfig = include __ROOT_DIR__ . '/config/mysql.php';
		try {
			parent::__construct('mysql:host=' . $mysqlConfig['host'] . ';dbname=' . $mysqlConfig['db_name'], $mysqlConfig['username'], $mysqlConfig['password']);
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		} catch(PDOException $e) {
			die('Unable to connect to Database.<br>' . var_dump($e));
		}
	}

    public function prepare($sql, $options = array())
    {
        $this->stmt = parent::prepare($sql, $options);
		return $this;
    }

    public function execute($params = array())
    {
        $this->stmt->execute($params);
		return $this;
    }

	public function fetchAll()
	{
		return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function fetch()
	{
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function fetchSingle()
	{
		return $this->stmt->fetch(PDO::FETCH_COLUMN);
	}
}