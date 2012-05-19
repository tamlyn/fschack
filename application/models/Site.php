<?php  

class Model_Site extends Model_Base{	
	public $id;
	private $_tableName = 'sites';
	
	
	function getId(){
		$q = "select site_id from site_alias a left join sites s on s.id = a.site_id where a.alias = ? and s.centre = ?";
		$query = $this->_db->prepare($q);
		$query->execute(array($this->title, $this->centre));
		$siteId = $query->fetch()->id;
		if(!$siteId){
			$q = "insert into sites (id, title) VALUES (?, ?);";
			$this->_db->exec($q);
			$q = "insert into site_alias (site_id, alias) VALUES (?, ?);";
			$this->_db->exec($q);
			$siteId = $this->_db::lastInsertId();
		}
		return $siteId;
	}
	
	
	
	

}

 ?>