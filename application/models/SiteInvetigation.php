<?php  

class Model_SiteInvestigation extends Model_Base{	
	public $id;
	private $_tableName = 'siteInvestigations';
	
	function getMeasurmentsByType(){
		$q = "select * from measurements where type = ? and siteInvestigationId = ?";
		return $this->executeQuery($q, array($type, $this->id));
	}
	
	function getDepths(){
		return $this->getMeasurementsByType('river_depth');
	}
	
	function getSiteId(){
		$q = "select site_id from site_alias a left join sites s on s.id = a.site_id where a.alias = ? and s.centre = ?";
		if(!$siteId = $this->executeQuery($q, array($this->title, $this->centre))){
			$q = "insert into sites (id, title) VALUES (?, ?);";
			$this->executeQuery($q);
			$q = "insert into site_alias (site_id, alias) VALUES (?, ?);";
			$siteId = $this->executeQuery($q);
		}
	}
	
	
	
	
	
	

}

 ?>