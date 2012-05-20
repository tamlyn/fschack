<?php  

class Model_SiteInvestigation extends Model_Base{	
	public $id;
	protected $_tableName = 'siteinvestigations';
	
	function getMeasurmentsByType(){
		$q = "select * from measurements where type = ? and siteInvestigationId = ?";
		return $this->executeQuery($q, array($type, $this->id));
	}
	
	function getDepths(){
		return $this->getMeasurementsByType('river_depth');
	}
	

}
