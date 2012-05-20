<?php  

class Model_SiteInvestigation extends Model_Base{	
	protected $_tableName = 'siteInvestigations';
	
	function getMeasurementsByType($type){
		$statement = $this->_db->prepare("select * from measurements where type = :type and siteInvestigationId = :id");
		$statement->execute(array(':type'=>$type, ':id'=>$this->id));

		return $statement->fetchAll();
	}
	
	function getDepths(){
		return $this->getMeasurementsByType('depth');
	}
	

}
