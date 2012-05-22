<?php  

class Model_SiteInvestigation extends Model_Base{	
	protected $_tableName = 'siteinvestigations';

	function getMeasurements() {
		return Model_Measurement::fetchAll('siteInvestigationId = :id', array(':id'=>$this->id));
	}

	function getMeasurementsByType($type, $allowNone = false){
		$measurements = Model_Measurement::fetchAll('type = :type and siteInvestigationId = :id', array(':type'=>$type, ':id'=>$this->id));
		if (!$measurements && !$allowNone) {
			$measurements = array(new Model_Measurement(array('value'=>0)));
		}
		return $measurements;
	}

	function getMeanMeasurement($measurements){
		$values =  array();
		foreach($measurements as $measurement){
			$values[] = $measurement->value;
		}
		if($values){
			return array_sum($values) / count($values);
		}
		return 0;
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
		return $this->getMeanMeasurement($this->getDepths());
	}

	function getFlowrates() {
		$flowrates = $this->getMeasurementsByType('flowrate_speed', true);
		if (!$flowrates) {
			$flowrates = $this->getMeasurementsByType('flowrate_revs');
			foreach ($flowrates as $flowrate) {
				$flowrate->value = 1/$flowrate->value; //TODO: How should this be converted?
			}
		}
		return $flowrates;
	}
	
	function getMeanFlowrate(){
		return $this->getMeanMeasurement($this->getFlowrates());
	}

	function getBedloadLengths(){
		return $this->getMeasurementsByType('bedload_length');
	}

	function getMeanBedloadLength(){
		return $this->getMeanMeasurement($this->getBedloadLengths());
	}
	function getRoundnesses(){
		return $this->getMeasurementsByType('roundness');
	}

	function getMeanRoundness(){
		return $this->getMeanMeasurement($this->getRoundnesses());
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
