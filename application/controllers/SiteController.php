<?php

class SiteController extends BaseController
{
	public function indexAction() {
		$this->view->sites = Model_Site::fetchAll();
	}

	public function deleteAction() {
		if ($this->_request->isPost()) {
			$site = Model_Site::fetchById($this->_request->id);
			if ($site) {
				$site->delete();
				$this->_helper->FlashMessenger(array('info'=>'Investigation deleted'));
			}
		}
		$this->_helper->redirector->gotoRoute(array('action'=>'index'));
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
				strtotime($siteInvestigation->investigation->startDate)*1000,
				$siteInvestigation->meanDepth,
//				$siteInvestigation->meanFlowrate,
				$siteInvestigation->discharge
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
		$site = Model_Site::fetchById($this->_request->id);

		$series = array();
		$maxDepth = $site->getMaxWaterWidth();
		foreach($site->siteInvestigations as $siteInvestigation) {
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
		$this->view->maxDepth = $site->maxDepth;
		$this->view->minDepth = $site->minDepth;
		$this->view->maxWaterWidth = $site->maxWaterWidth;
		$this->view->minWaterWidth = $site->minWaterWidth;

		$this->view->site = $site;
	}


}

