<?php  

class Model_Investigation extends Model_Base{	
	protected $_tableName = 'investigations';

	static function insert($data){
		$investigation = new Model_Investigation();
		$investigation->name = $data->name;
		$investigation->startDate = date('Y-m-d', strtotime($data->date));
		$investigation->schoolName = $data->school;
		$investigation->centre = $data->centre;
		$investigation->save();

		foreach($data->siteInvestigations as $i => $siteInvestigation){
			$site = Model_Site::fetchByCentreAndTitle($investigation->centre, $siteInvestigation->site_name);
			if(!$site){
				$site = new Model_Site();
				$site->centre = $investigation->centre;
				$site->title = $siteInvestigation->site_name;
				$site->save();
			}
			$siteInv = new Model_SiteInvestigation();
			$siteInv->siteId = $site->id;
			$siteInv->investigationId = $investigation->id;
			$siteInv->siteOrder = $i;
			$siteInv->save();

			foreach($siteInvestigation->data as $type => $values){
				foreach($values as $i => $value){
					$measurement = new Model_Measurement();
					$measurement->siteInvestigationId = $siteInv->id;
					$measurement->type = $type;
					$measurement->investigationSeriesIndex = $i;
					$measurement->value = $value;
					$measurement->save();
				}
			}
		}

	}

	public function getSiteInvestigations() {
		return Model_SiteInvestigation::fetchAll('investigationId = :id', array(':id'=>$this->id));
	}

	public function getMax($type){
		$siteInvestigations = $this->getSiteInvestigations();
		$fname = str_replace(' ','', ucwords(implode(' ', explode('_', $type))));
		$maxProperty = "max".$fname;
		$minProperty = "min".$fname;
		$this->$maxProperty =0;
		$this->$minProperty =99999;
		foreach($siteInvestigations as $si){
			foreach($si->getMeasurementsByType($type) as $value){
				if(($value->value > $this->$maxProperty) && $value->value){
					$this->$maxProperty = $value->value;
				}
				if(($value->value < $this->$minProperty) && $value->value){
					$this->$minProperty = $value->value;
				}

			}
		}
		return $this->$maxProperty;
	}

	public function getMin($type){
		$maxFunctionName = "max".str_replace(' ','', ucwords(implode(' ', explode('_', $type))));
		$minPropertyName = "min".str_replace(' ','', ucwords(implode(' ', explode('_', $type))));
		$this->$maxFunctionName();
		return $this->$minPropertyName;
	}


	public function getMinDepth(){
		return (float)$this->getMin('getMaxDepth', 'minDepth');
	}

	public function getMaxDepth(){
		return (float)$this->getMax('depth');
	}

	public function getMinWaterWidth(){
		return (float)$this->getMin('water_width');
	}

	public function getMaxWaterWidth(){
		return (float)$this->getMax('water_width');
	}

	// only use this for display purposes
	public function getDateString() {
		return date('d M Y', strtotime($this->startDate));
	}

	public function delete() {
		foreach ($this->getSiteInvestigations() as $siteInvestigation) {
			$siteInvestigation->delete();
		}
		parent::delete();
	}

}
