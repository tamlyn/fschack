<?php

class InvestigationController extends BaseController
{
	public function indexAction() {
		$this->view->investigations = Model_Investigation::fetchAll();
	}

	public function viewAction() {
		$investigation = Model_Investigation::fetchById($this->_request->id);

//		$siteData = array();
//		foreach ($this->view->investigation->siteInvestigations[0] as $site) {
//			$siteData[] = ;
//		}

		$graphData = array();
		foreach ($investigation->siteInvestigations[0]->getDepths() as $i => $measurement) {
			$graphData[] = array("$i", floatval($measurement['value']));
		}

		$this->view->graphData = array(
			'type' => 'depth',
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

