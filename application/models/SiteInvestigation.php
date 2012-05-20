<?php  

class Model_SiteInvestigation extends Model_Base{	
	public $id;
	protected $_tableName = 'siteinvestigations';
	
	function getMeasurementsByType($type){
		$statement = $this->_db->prepare("select * from measurements where type = :type and siteInvestigationId = :id");
		$statement->execute(array(':type'=>$type, ':id'=>$this->id));

		return $statement->fetchAll();
	}
	
	function getDepths(){
		$depths = $this->getMeasurementsByType('depth');
		array_push($depths, array('value' => 0));
		array_unshift($depths, array('value' => 0));
		return $depths;
	}
	

}
