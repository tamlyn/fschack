<?php  

class Model_SiteInvestigation extends Model_Base{	
	protected $_tableName = 'siteinvestigations';

	function getMeasurements() {
		return Model_Measurement::fetchAll('siteInvestigationId = :id', array(':id'=>$this->id));
	}

	function getMeasurementsByType($type){
		return Model_Measurement::fetchAll('type = :type and siteInvestigationId = :id', array(':type'=>$type, ':id'=>$this->id));
	}

	function getMeanMeasurement($type){
		$values =  array();
		foreach($this->getMeasurementsByType($type) as $value){
			$values[] = $value->value;
		}
		if($values){
			return array_sum($values) / count($values);
		}
	}

	function getSite(){
		return Model_Site::fetchById($this->siteId);
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
	function getRoundesses(){
		return $this->getMeasurementsByType('roundness');
	}

	function getMeanRoundess(){
		return $this->getMeanMeasurement('roundness');
	}
	function getWidth(){
		return array_shift($this->getMeasurementsByType('water_width'));
	}

	function getWettedPerimeter(){
		return array_shift($this->getMeasurementsByType('wetted_perimeter'));
	}
	function getGradientDegrees(){
		return array_shift($this->getMeasurementsByType('gradient_degrees'));
	}

	function getCsa(){
		return $this->meanFlowRate * $this->meanDepth;
	}

	function getDischarge(){
		return $this->csa * $this->meanFlowRate;
	}

	function getHydraulicRadius(){
		return $this->csa / $this->wettedPerimeter->value;
	}

	public function delete() {
		foreach ($this->getMeasurements() as $measurement) {
			$measurement->delete();
		}
		parent::delete();
	}

}
