<?php

class InvestigationController extends BaseController
{
	public function indexAction() {
		$this->view->investigations = Model_Investigation::fetchAll();
	}

	public function deleteAction() {
		if ($this->_request->isPost()) {
			$investigation = Model_Investigation::fetchById($this->_request->id);
			if ($investigation) {
				$investigation->delete();
				$this->_helper->FlashMessenger(array('info'=>'Investigation deleted'));
			}
		}
		$this->_helper->redirector->gotoRoute(array('action'=>'index'));
	}

	public function overviewAction() {
		$investigation = Model_Investigation::fetchById($this->_request->id);
		$this->view->investigation = $investigation;
	}

	public function sitesAction() {
		$investigation = Model_Investigation::fetchById($this->_request->id);

		$series = array();
		$maxDepth = $investigation->getMaxWaterWidth();
		foreach($investigation->siteInvestigations as $siteInvestigation) {
			$series[] = $this->makeDepthSeries($siteInvestigation, $maxDepth);
		}

		$this->view->graphData = array(
			'type' => 'depth',
			'options' => array(
				'hAxis' => array(
					'title'=>'River width (m)',
//					'minValue'=>-$margin,
//					'maxValue'=>$maxWidth
				),
				'vAxis'=>array(
					'title'=>'Depth (m)',
					'direction'=>-1
				),
				'legend'=>array(
					'position'=>'none'
				),
				'animation' => array(
					'duration' => 1000,
		            'easing' => 'out'
				)
			),
			'series' => $series
		);
		$this->view->meanFlowrate = $siteInvestigation->getMeanFlowrate();
		$this->view->meanDepth = $siteInvestigation->getMeanDepth();
		$this->view->maxDepth = $investigation->maxDepth;
		$this->view->minDepth = $investigation->minDepth;
		$this->view->maxWaterWidth = $investigation->maxWaterWidth;
		$this->view->minWaterWidth = $investigation->minWaterWidth;

		$this->view->investigation = $investigation;
	}

	protected function makeDepthSeries($siteInvestigation, $maxWidth) {
		$margin = ($maxWidth - $siteInvestigation->width->value) / 2;
		$series = array();
		$numPoints = count($siteInvestigation->depths);

		$series[] = array(-$margin, 0);
		foreach ($siteInvestigation->depths as $i => $measurement) {
			$series[] = array(
				($siteInvestigation->width->value / ($numPoints - 1) * $i), floatval($measurement->value)
			);
		}
		$series[] = array($maxWidth, 0);

		return $series;
	}

	public function exportAction() {
		$investigation = Model_Investigation::fetchById($this->_request->id);
		$filename = urlencode(str_replace(' ', '_', $investigation->schoolName))."-".$investigation->startDate.'-export.csv';
		//$data = $investigation->toArray();

//		$this->_response->setHeader('Content-type', 'application/octet-stream');
//		$this->_response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');


		//make header
		$rows['headers'] = array('Site:');
		$rows['siteNames'] = array('Site name:');
		$rows['siteWidths'] = array('Widths:');
		$rows['sitePerimeters'] = array('Wetted perimeter:');
		foreach($investigation->getSiteInvestigations() as $i => $siteInvestigation){
			$rows['headers'][] = $i;
			$rows['siteNames'][]  = $siteInvestigation->site->title;
			$rows['siteWidths'][] = $siteInvestigation->width->value;
			$rows['sitePerimeters'][] = $siteInvestigation->wettedPerimeter->value;
			foreach($siteInvestigation->depths as $j => $depth){
				if(!isset($rows['siteWidth'.($j+1)])){
					$rows['siteWidth'.($j+1)] = array();
					$rows['siteWidth'.($j+1)][] = "Depth ".($j+1).":";
				}else{
					$rows['siteWidth'.($j+1)][] = $depth->value;
				}
			}
			if(!isset($rows['meanDepth']))
				$rows['meanDepth'] = array('Mean Depth:');
			$rows['meanDepth'][] = $siteInvestigation->meanDepth;

			foreach($siteInvestigation->flowRates as $j => $rate){
				if(!isset($rows['flowRates'.($j+1)])){
					$rows['flowRates'.($j+1)] = array();
					$rows['flowRates'.($j+1)][] = "Flowrate ".($j+1).":";
				}else{
					$rows['flowRates'.($j+1)][] = $rate->value;
				}
			}
			if(!isset($rows['meanFlowRate']))
				$rows['meanFlowRate'] = array('Mean Flow Rate:');
			$rows['meanFlowRate'][] = $siteInvestigation->meanFlowRate;

			if(!isset($rows['csa']))
				$rows['csa'] = array('C.S.A. (m^2):');
			$rows['csa'][] = $siteInvestigation->csa;


			if(!isset($rows['discharge']))
				$rows['discharge'] = array('Discharge (m^3/s):');
			$rows['discharge'][] = $siteInvestigation->discharge;

			if(!isset($rows['hydraulic_radius']))
				$rows['hydraulic_radius'] = array('Hydraulic Radius:');
			$rows['hydraulic_radius'][] = $siteInvestigation->hydraulicRadius;


		}
		print_r($rows);
		foreach($rows as $row){
			echo implode(',', $row) . "\n";

		}


//
//		foreach ($data as $row) {
//			//quote data
//			$quotedRow = array();
//			foreach ($row as $cell) {
//				$quotedRow[] = '"' . str_replace('"', '""', $cell) . '"';
//			}
//			//output immediately (to save memory)
//			echo implode(',', $quotedRow) . "\n";
//		}

		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	}
}

