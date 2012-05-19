<?php  

class Model_Investigation extends Model_Base{	
	protected $_tableName = 'investigations';
	public $date;
	
	static function insert($data){
		$investigation = new Model_Investigation($data);
		$investigation->save();
		$sites = array();
		foreach($data->siteInvestigations as $siteInvestigation){
			print_r($siteInvestigation);
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
			$siteInv->save();

			foreach($siteInvestigation->data as $type => $values){
//				$q = "INSERT INTO measurements (siteInvestigationId, type, investigationSeriesIndex, value) VALUES (?,?,?,?)";
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
	

}

 ?>