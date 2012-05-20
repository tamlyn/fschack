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
		$site = Model_Site::fetchById($this->_request->id);
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

