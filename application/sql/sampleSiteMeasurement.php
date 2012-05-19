<?php  

class siteInvestigation(){	
	public $id;
	
	function getMeasurmentsByType(){
		$q = "select * from measurements where type = ? and siteInvestigationId = ?";
		return $this->executeQuery($q, array($type, $this->id));
	}
	
	function getDepths(){
		return $this->getMeasurementsByType('river_depth');
	}
	
	function save($parameters){
		$q = "insert into siteInvestigations (siteId, groupId, timestamp) VALUES (?, ?, ?)";
		$this->id = $this->executeQuery($q);
	}
	

}

 ?>