<?php  

class Model_Investigation extends Model_Base{	
	protected $_tableName = 'investigations';

	static function insert($data){
		$investigation = new Model_Investigation();
		$investigation->startDate = $data->date;
		$investigation->schoolName = $data->school;
		$investigation->centre = $data->centre;
		$investigation->save();

		foreach($data->siteInvestigations as $siteInvestigation){
			$site = Model_Site::fetchByCentreAndTitle($investigation->centre, $siteInvestigation->site_name);
			if(!$site){
				$site = new Model_Site();
				$site->centre = $investigation->centre;
				$site->save();
			}
			$siteInv = new Model_SiteInvestigation();
			$siteInv->siteId = $site->id;
			$siteInv->investigationId = $investigation->id;
			$siteInv->save();

			foreach($siteInvestigation->data as $type => $values){
//				$$this->investigation->siteInvestigations[0]->depthsq = "INSERT INTO measurements (siteInvestigationId, type, investigationSeriesIndex, value) VALUES (?,?,?,?)";
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

}
