<?php


abstract class Model_Base
{
	protected static $columns = array();
	
	protected $_db, $_row, $_tableName, $_sortColumn = 1, $_isNew;
	
	public function __construct($data = null, $fromDb = false)
	{
		$this->_db = Zend_Registry::get('db')->getConnection();
		$this->_row = array();
		$this->_isNew = !$fromDb;

		if (is_array($data) || is_object($data)) {
			$this->fromArray($data);
		}
	}

	static function insert($data){
		$me = new static($data);
		$me->save();
	}

	public function getColumnNames()
	{
		$classname = get_called_class();
		if (!array_key_exists($classname, self::$columns)) {
			self::$columns[$classname] = array();
			$statement = $this->_db->prepare('SELECT column_name AS name FROM information_schema.columns WHERE table_schema=database() AND table_name=:tableName');
			$statement->execute(array(':tableName'=>$this->_tableName));
			foreach ($statement as $row) {
				self::$columns[$classname][] = $row['name'];
			}
		}
		return self::$columns[$classname];
	}
	
	public function __get($name)
	{
		$methodName = 'get'.ucfirst($name);
		if (method_exists($this, $methodName)) {
			return $this->$methodName();
		} elseif (in_array($name, $this->columnNames)) {
			return isset($this->_row[$name]) ? $this->_row[$name] : null;
		}
		return null;
	}
	
	public function __set($name, $value)
	{
		$methodName = 'set'.ucfirst($name);
		if (method_exists($this, $methodName)) {
			return $this->$methodName($value);
		} elseif (in_array($name, $this->columnNames)) {
			return $this->_row[$name] = $value;
		} else {
			return $this->$name = $value;
		}
	}
	
	public function find($id)
	{
		$statement = $this->_db->prepare('SELECT * FROM '.$this->_tableName.' WHERE id = ?');
		$statement->execute(array($id));
		if (!count($statement)) {
			throw new RuntimeException('ID not found in database');
		}
		$this->fromArray($statement->fetch(PDO::FETCH_ASSOC));
		$this->_isNew = false;
	}
	
	public function save()
	{
		$data = $this->_row;
		
		if ($this->_isNew) {
			$query = 'INSERT INTO '.$this->_tableName.' '.
				'(`'.implode('`,`', array_keys($data)).'`) '.
				'VALUES ('.implode(',', array_fill(0, count($data), '?')).')';
		} else {
			$query = 'UPDATE '.$this->_tableName.' '.
				'SET '.implode('=?, ', array_keys($data)).'=? '.
				'WHERE id=?';
			//add id to fill last placeholder
			$data[] = $this->id;
		}
		
		$statement = $this->_db->prepare($query);
		$statement->execute(array_values($data));

		if ($this->_isNew) {
			$this->id = $this->_db->lastInsertId();
			$this->_isNew = false;
		}
				
	}
	
	public function delete()
	{
		$statement = $this->_db->prepare('DELETE FROM '.$this->_tableName.' WHERE id = ?');
		$statement->execute(array($this->id));
		$this->_row = array();
		$this->_isNew = true;
	}
	
	public function deleteIfEmpty()
	{
		foreach ($this->toArray() as $name=>$value) {
			if ($name != 'id' && $value) {
				return false;
			}
		}
		$this->delete();
		return true;
	}
	
	public function toArray($extraColumns = array())
	{
		$row = $this->_row;
		foreach ($extraColumns as $col) {
			$row[$col] = $this->$col;
		}
		return $row;
	}
	
	public function fromArray($data)
	{
		if ($data) {
			foreach ((array) $data as $key => $value) {
				if (in_array($key, $this->columnNames)) {
					$this->{$key} = $value;
				}
			}
		}
	}

	public static function fetchById($id) {
		if (!is_scalar($id)) {
			return null;
		}
		return self::fetchBy('id', $id);
	}

	public static function fetchBy($col, $key) {
		if ($key && is_scalar($key)) {
			$classname = get_called_class();
			$class = new $classname;
			$objects = $class->fetch(addslashes($col)." = ?", array($key));
			return ($objects ? $objects[0] : null);
		} else {
			return null;
		}
	}

	public static function fetchAll($clause = null, $args = array()) {
		$classname = get_called_class();
		$class = new $classname;
		return $class->fetch($clause, $args);
	}
	
	protected function fetch($clause = null, $args = array()) {
		$sql = 'SELECT * FROM '.$this->_tableName;
		if ($clause) {
			$sql .= ' WHERE ' . $clause;
		}
		$sql .= ' ORDER BY '.$this->columnNames[$this->_sortColumn];

		$statement = $this->_db->prepare($sql);
		$statement->execute($args);
		
		return $this->objectify($statement);
	}
	
	public static function objectify($data) {
		$classname = get_called_class();
		$objects = array();
		foreach ($data as $row) {
			$objects[] = new $classname($row, true);
		}
		return $objects;
	}
	
	public static function insertData($tableName, $data) {
		$newCount = 0;

		$table = new Zend_Db_Table($tableName);

		foreach ($data as $item) {
			try {
				$table->insert($item);
				$newCount++;
			} catch (Zend_Db_Statement_Exception $e) {
				// ignore duplicate entry violations
				if ($e->getCode() != 23000 || strpos($e->getMessage(), '1062 Duplicate entry') === false) {
					// echo "cannot insert data into $tableName: ", print_r($item, true), "<br />\n";
					throw $e;
				}
			}
		}

		return $newCount;
	}

	// generates a sql ordering string from array $cols (name=>direction). any whose name
	// isn't in $validCols is ignored. If none are valid, empty string is returned
	protected static function generateOrderingString($cols, $validCols) {
		$c = array();
		foreach ($cols as $col=>$dir) {
			if (array_key_exists($col, $validCols)) {
				$c[] = $validCols[$col] . ' ' . $dir;
			}
		}
		
		return $c ? ' ORDER BY ' . implode(', ', $c) : '';
	}

	public static function localeDate($datetime) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $datetime, new DateTimeZone('UTC'));
		$date->setTimezone(new DateTimeZone(date_default_timezone_get()));
		return $date->format('Y-m-d H:i:s');
	}


	public function getMax($type){
		$siteInvestigations = $this->getSiteInvestigations();
		$fname = str_replace(' ','', ucwords(implode(' ', explode('_', $type))));
		$maxProperty = "max".$fname;
		$minProperty = "min".$fname;
		$this->$maxProperty =-99999999;
		$this->$minProperty =999999999;
		foreach($siteInvestigations as $si){
			foreach($si->getMeasurementsByType($type) as $value){
				if(($value->value > $this->$maxProperty) && is_numeric($value->value)){
					$this->$maxProperty = $value->value;
				}
				if(($value->value < $this->$minProperty) && is_numeric($value->value)){
					$this->$minProperty = $value->value;
				}

			}
		}
		return $this->$maxProperty;
	}

	public function getMin($type){
		$maxFunctionName = "max".str_replace(' ','', ucwords(implode(' ', explode('_', $type))));
		$minPropertyName = "min".str_replace(' ','', ucwords(implode(' ', explode('_', $type))));
		$this->$maxFunctionName();
		return $this->$minPropertyName;
	}


	public function getMinDepth(){
		return (float)$this->getMin('getMaxDepth', 'minDepth');
	}

	public function getMaxDepth(){
		return (float)$this->getMax('depth');
	}

	public function getMinWaterWidth(){
		return (float)$this->getMin('water_width');
	}

	public function getMaxWaterWidth(){
		return (float)$this->getMax('water_width');
	}
}
