<?php

class InvestigationController extends BaseController
{
	public function indexAction() {
		$this->view->title = 'Investigations';
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
		$this->view->title = 'Investigation Overview';
		$investigation = Model_Investigation::fetchById($this->_request->id);
		$this->validateData($investigation);
		$series1 = array(
			'title' => $this->view->title,
			'columns' => array(
				array('type'=>'string', 'label'=>'Site'),
				array('type' => 'number', 'label' => 'Mean depth'),
				array('type' => 'number', 'label' => 'Mean flowrate')
			),
			'points' => array()
		);
		foreach ($investigation->siteInvestigations as $siteInvestigation) {
			$series1['points'][] = array(
				$siteInvestigation->site->title,
				$siteInvestigation->meanDepth,
				$siteInvestigation->meanFlowrate,
			);
		}

		$this->view->graphData = array(
			'options' => array(
				'hAxis' => array(
					'title' => 'Sites',
//					'minValue'=>-$margin,
//					'maxValue'=>$maxWidth
				),
				'vAxes' => array(
					array(
						'title' => 'Mean depth (m)',
//						'direction'=>-1
					),
					array(
						'title' => 'Mean flowrate (m/s)'
					)
				),
				'series' => array(
					1 => array(
						'targetAxisIndex' => 1
					)
				),
				'legend' => array(
//					'position' => 'none'
				),
				'animation' => array(
					'duration' => 1000,
					'easing' => 'out'
				)
			),
			'series' => array($series1)
		);
		$this->view->investigation = $investigation;
	}

	public function editAction() {
		$this->view->title = 'Edit Investigation';
		$investigation = Model_Investigation::fetchById($this->_request->id);
		$this->validateData($investigation);

		$this->view->investigation = $investigation;
		if ($this->_request->isPost()) {
			$investigation->fromArray($this->_request->getParams());
			$investigation->startDate = date('Y-m-d', strtotime($this->_request->date));
			$investigation->save();
			$this->_helper->FlashMessenger(array('info'=>'Investigation saved'));
			$this->_helper->redirector->gotoRoute(array('action'=>'overview'));
		}
	}

	public function sitesAction() {
		$investigation = Model_Investigation::fetchById($this->_request->id);

		$series = array();
		$maxDepth = $investigation->getMaxWaterWidth();
		foreach($investigation->siteInvestigations as $siteInvestigation) {
			$series[] = $this->makeDepthSeries($siteInvestigation, $maxDepth);
		}

		$this->view->graphData = array(
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
		$series = array(
			'title' => $siteInvestigation->site->title . ' ' . $siteInvestigation->investigation->startDate,
			'columns' => array(
				array('type' => 'number', 'label' => 'Width'),
				array('type' => 'number', 'label' => 'Depth'),
			),
			'points' => array()
		);
		$numPoints = count($siteInvestigation->depths);

		$series['points'][] = array(-$margin, 0);
		foreach ($siteInvestigation->depths as $i => $measurement) {
			$series['points'][] = array(
				($siteInvestigation->width->value / ($numPoints - 1) * $i),
				floatval($measurement->value)
			);
		}
		$series['points'][] = array($maxWidth, 0);

		return $series;
	}

	public function exportAction() {
		$investigation = Model_Investigation::fetchById($this->_request->id);
		$filename = urlencode(str_replace(' ', '_', $investigation->schoolName))."-".$investigation->startDate.'-export.csv';
		//$data = $investigation->toArray();

		$this->_response->setHeader('Content-type', 'application/octet-stream');
		$this->_response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');


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


			foreach($siteInvestigation->bedloadLengths as $j => $length){
				if(!isset($rows['bedloadLengths'.($j+1)])){
					$rows['bedloadLengths'.($j+1)] = array();
					$rows['bedloadLengths'.($j+1)][] = "Bedload Length ".($j+1).":";
				}else{
					$rows['bedloadLengths'.($j+1)][] = $length->value;
				}
			}

			if(!isset($rows['meanBedloadLength']))
				$rows['meanBedloadLength'] = array('Mean Bedload Length:');
			$rows['meanBedloadLength'][] = $siteInvestigation->hydraulicRadius;

/*
			foreach($siteInvestigation->roundnesses as $j => $roundness){
				if(!isset($rows['roundnesses'.($j+1)])){
					$rows['roundnesses'.($j+1)] = array();
					$rows['roundnesses'.($j+1)][] = "Roundness ".($j+1).":";
				}else{
					$rows['roundnesses'.($j+1)][] = $roundness->value;
				}
			}

			if(!isset($rows['meanRoundness']))
				$rows['meanRoundness'] = array('Mean Roundness:');
			$rows['meanRoundness'][] = $siteInvestigation->meanRoundess;
			*/



		}
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

