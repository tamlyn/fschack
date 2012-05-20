<?php  

class Model_SiteInvestigation extends Model_Base{	
	protected $_tableName = 'siteinvestigations';
	
	function getMeasurementsByType($type){
		return Model_Measurement::fetchAll('type = :type and siteInvestigationId = :id', array(':type'=>$type, ':id'=>$this->id));
	}

	function getMeanMeasurement($type){
		$values =  array();
		foreach($this->getMeasurementsByType($type) as $value){
			$values[] = $value['value'];
		}
		if($values){
			return array_sum($values) / count($values);
		}
	}

	function getDepths(){
		$this->depths = $this->getMeasurementsByType('depth');
		array_push($this->depths, new Model_Measurement(array('value' => 0)));
		array_unshift($this->depths, new Model_Measurement(array('value' => 0)));
		return $this->depths;
	}
	function getMeanDepth(){
		return $this->getMeanMeasurement('depth');
	}

	function getWidth() {
		$measurement = array_shift($this->getMeasurementsByType('water_width'));
		return $this->width = $measurement->value;
	}

	function getFlowrates() {
		return $this->getMeasurementsByType('flowrate');
	}
	
	function getMeanFlowrate(){
		return $this->getMeanMeasurement('flowrate');
	}

	function getBedloadLengths(){
		return $this->getMeasurementsByType('bedload_length');
	}

	function getMeanBedloadLength(){
		return $this->getMeanMeasurement('bedload_length');
	}
	function getWidth(){
		return array_shift($this->getMeasurementsByType('water_width'));
	}

	function getWettedPerimiter(){
		return array_shift($this->getMeasurementsByType('wetted_perimeter'));
	}
	function getGradientDegrees(){
		return array_shift($this->getMeasurementsByType('gradient_degrees'));
	}


}
