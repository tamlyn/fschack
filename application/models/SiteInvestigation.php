<?php  

class Model_SiteInvestigation extends Model_Base{	
	public $id;
	protected $_tableName = 'siteinvestigations';
	
	function getMeasurementsByType($type){
		$statement = $this->_db->prepare("select * from measurements where type = :type and siteInvestigationId = :id");
		$statement->execute(array(':type'=>$type, ':id'=>$this->id));

		return $statement->fetchAll();
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
		return $this->getMeasurementsByType('depth');
	}
	function getMeanDepth(){
		return $this->getMeanMeasurement('depth');
	}

	function getFlowrates(){
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
