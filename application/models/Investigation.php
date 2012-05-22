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

			foreach($siteInvestigation->measurements as $type => $values){
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
		$this->siteInvestigations = Model_SiteInvestigation::fetchAll('investigationId = :id', array(':id'=>$this->id));
		usort($this->siteInvestigations, function($a, $b) { return $a->siteOrder > $b->siteOrder ? 1 : -1; });
		return $this->siteInvestigations;
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
