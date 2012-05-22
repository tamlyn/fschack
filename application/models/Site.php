<?php  

class Model_Site extends Model_Base{	
	protected $_tableName = 'sites';

	public function save()
	{
		parent::save();
		$q = "insert into sitealias (site_id, alias, centre) VALUES (?, ?, ?)";
		$stmt = $this->_db->prepare($q)->execute(array($this->id, $this->title, $this->centre));
	}

	static function fetchByCentreAndTitle($centre, $title){
		$q = "select * from sitealias a left join sites s on s.id = a.site_id where s.centre = ? and a.alias = ?";
		$db = Zend_Registry::get('db')->getConnection();
		$query = $db->prepare($q);
		$query->execute(array($centre, $title));
		$site = $query->fetch();
		if($site){
			return new Model_Site($site);
		}else{
			return null;
		}
	}


	public function getSiteInvestigations() {
		$this->siteInvestigations = Model_SiteInvestigation::fetchAll('siteId = :id', array(':id'=>$this->id));
		usort($this->siteInvestigations, function ($a, $b) {
			$aVal = $a->investigation->startDate;
			$bVal = $b->investigation->startDate;
			return strcmp($aVal, $bVal);
		});
		return $this->siteInvestigations;
	}

	public function delete() {
		foreach ($this->siteInvestigations as $siteInvestigation) {
			$siteInvestigation->delete();
		}
		parent::delete();
	}

	/*
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
	}*/
	
	
	
	

}
