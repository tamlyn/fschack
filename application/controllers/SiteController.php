<?php

class SiteController extends BaseController
{
	public function indexAction() {
		$this->view->title = 'All Sites';
		$this->view->sites = Model_Site::fetchAll();
	}

	public function deleteAction() {
		if ($this->_request->isPost()) {
			$site = Model_Site::fetchById($this->_request->id);
			if ($site) {
				$site->delete();
				$this->_helper->FlashMessenger(array('info'=>'Site deleted'));
			}
		}
		$this->_helper->redirector->gotoRoute(array('action'=>'index', 'id'=>null));
	}

	public function overviewAction() {
		$this->view->title = 'Site overview';
		$site = Model_Site::fetchById($this->_request->id);
		$this->view->title = $site->centre . ' - '. $site->title;

		$series1 = array(
			'title' => $this->view->title,
			'columns' => array(
				array('type' => 'date', 'label' => 'Date'),
				array('type' => 'number', 'label' => 'Mean depth'),
//				array('type' => 'number', 'label' => 'Mean flowrate'),
				array('type' => 'number', 'label' => 'Discharge')
			),
			'points' => array()
		);
		foreach ($site->siteInvestigations as $siteInvestigation) {
			$series1['points'][] = array(
				(strtotime($siteInvestigation->investigation->startDate)+6000)*1000,
				round($siteInvestigation->meanDepth, 2),
//				$siteInvestigation->meanFlowrate,
				round($siteInvestigation->discharge, 2)
			);
		}

		$this->view->graphData = array(
			'options' => array(
				'hAxis' => array(
					'title' => 'Date',
//					'minValue'=>-$margin,
//					'maxValue'=>$maxWidth
				),
				'vAxes' => array(
					array(
						'title' => 'Mean depth (m)',
//						'direction'=>-1
					),
//					array(
//						'title' => 'Mean flowrate (m/s)'
//					),
					array(
						'title' => 'Discharge (m3/s)'
					),
				),
				'series' => array(
					array(
						'color' => '#CD3667'
					),
					array(
						'targetAxisIndex' => 1,
						'color' => '#47918E'
					),
					array(
						'targetAxisIndex' => 2
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

		$this->view->site = $site;
	}


	public function investigationsAction() {
		$this->view->site = Model_Site::fetchById($this->_request->id);
		$this->view->graphData = $this->makeRiverJourneyData($this->view->site);
	}


}

