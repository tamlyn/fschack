<?php  

class Model_SiteInvestigation extends Model_Base{	
	protected $_tableName = 'siteinvestigations';
	
	function getMeasurementsByType($type){
		return Model_Measurement::fetchAll('type = :type and siteInvestigationId = :id', array(':type'=>$type, ':id'=>$this->id));
	}
	
	function getDepths(){
		$depths = $this->getMeasurementsByType('depth');
		array_push($depths, new Model_Measurement(array('value' => 0)));
		array_unshift($depths, new Model_Measurement(array('value' => 0)));
		return $depths;
	}

	function getWidth() {
		return array_shift($this->getMeasurementsByType('water_width'));
	}

}
