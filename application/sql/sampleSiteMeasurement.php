<?php  

class siteInvestigation(){	
	public $id;
	
	function __construct($data = null){
		if(is_object($data)){
			$this->save($data);
		}elseif(is_int($data){
			$this->load($id);
		}
	}
	
	function getMeasurmentsByType(){
		$q = "select * from measurements where type = ? and siteInvestigationId = ?";
		return $this->executeQuery($q, array($type, $this->id));
	}
	
	function getDepths(){
		return $this->getMeasurementsByType('river_depth');
	}
	
	function save($parameters){
		$siteId = $this->getSiteId();
	
		$q = "insert into siteInvestigations (siteId, groupId, timestamp) VALUES (?, ?, ?)";
		$this->id = $this->executeQuery($q, array($siteId, $groupId, time()));
	}
	
	function getSiteId(){
		$q = "select site_id from site_alias a left join sites s on s.id = a.site_id where a.alias = ? and s.centre = ?";
		if(!$siteId = $this->executeQuery($q, array($this->title, $this->centre))){
			$q = "insert into sites (id, title) VALUES (?, ?);";
			$q = "insert into site_alias (site_id, alias) VALUES (?, ?);";
			$siteId = $this->executeQuery($q);
		}
	}
	
	
	
	
	

}

 ?>