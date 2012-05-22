<?php  

class Model_SiteInvestigation extends Model_Base{	
	protected $_tableName = 'siteinvestigations';

	function getMeasurements() {
		return Model_Measurement::fetchAll('siteInvestigationId = :id', array(':id'=>$this->id));
	}

	function getMeasurementsByType($type){
		$measurements = Model_Measurement::fetchAll('type = :type and siteInvestigationId = :id', array(':type'=>$type, ':id'=>$this->id));
		if (!$measurements) {
			$measurements = array(new Model_Measurement(array('value'=>0)));
		}
		return $measurements;
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

	public function getSite() {
		$this->site = Model_Site::fetchById($this->siteId);
		return $this->site;
	}

	public function getInvestigation() {
		$this->investigation = Model_Investigation::fetchById($this->investigationId);
		return $this->investigation;
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
		return $this->getMeasurementsByType('flowrate_speed');
	}
	
	function getMeanFlowrate(){
		return $this->getMeanMeasurement('flowrate_speed');
	}

	function getBedloadLengths(){
		return $this->getMeasurementsByType('bedload_length');
	}

	function getMeanBedloadLength(){
		return $this->getMeanMeasurement('bedload_length');
	}
	function getRoundnesses(){
		return $this->getMeasurementsByType('roundness');
	}

	function getMeanRoundness(){
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
