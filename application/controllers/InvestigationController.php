<?php

class InvestigationController extends BaseController
{
	public function indexAction() {
		$this->view->investigations = Model_Investigation::fetchAll();
	}

	public function overviewAction() {
		$investigation = Model_Investigation::fetchById($this->_request->id);
		$siteData = array();
		foreach ($this->view->investigation->siteInvestigations[0] as $site) {
//			$siteData[] = ;
		}
		$this->view->investigation = $investigation;
	}

	public function sitesAction() {
		$investigation = Model_Investigation::fetchById($this->_request->id);

		$siteInvestigation = $investigation->siteInvestigations[0];
		$maxWidth = 10;
		$margin = ($maxWidth - $siteInvestigation->width) / 2;
		$graphData = array();
		$numPoints = count($siteInvestigation->depths);

		$graphData[] = array(-$margin, 0);
		foreach ($siteInvestigation->depths as $i => $measurement) {
			$graphData[] = array(($siteInvestigation->width/($numPoints-1)*$i), floatval($measurement->value));
		}
		$graphData[] = array($maxWidth, 0);

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
			),
			'series' => $graphData
		);
		$this->view->investigation = $investigation;
	}

	public function exportAction() {
		$investigation = Model_Investigation::fetchById($this->_request->id);

		$filename = 'export.csv';
		$data = $investigation->toArray();

		$this->_response->setHeader('Content-type', 'application/octet-stream');
		$this->_response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');


		//make header
		if ($data) {
			$headers = array_keys($data[0]);
			$headers = array_map(function($name) {
				return ucfirst(str_replace('_', ' ', $name));
			}, $headers);
			echo implode(',', $headers) . "\n";
		}

		foreach ($data as $row) {
			//quote data
			$quotedRow = array();
			foreach ($row as $cell) {
				$quotedRow[] = '"' . str_replace('"', '""', $cell) . '"';
			}
			//output immediately (to save memory)
			echo implode(',', $quotedRow) . "\n";
		}

		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	}
}

