<?php  

class Model_SiteInvestigation extends Model_Base{	
	public $id;
	protected $_tableName = 'siteInvestigations';

	
	function getMeasurmentsByType($type){
		$q = "select * from measurements where type = ? and siteInvestigationId = ?";
		$stmt = $this->_db->prepare($q)->execute(array($type, $this->id));
		return $stmt->fetchAll();
	}
	
	function getDepths(){
		return $this->getMeasurementsByType('river_depth');
	}
	

}

 ?>