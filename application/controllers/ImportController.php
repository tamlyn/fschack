<?php

require_once 'lib/Excel/reader.php';

class ImportController extends BaseController
{
	public function indexAction() {
		$this->view->sites = array();
		if ($this->_request->isPost()) {
			if (!isset($_FILES['file'])) {
				$this->_helper->FlashMessenger(array('error'=>'Please select an excel file'));
			} else {
				$fileInfo = $_FILES['file'];
				if ($fileInfo['error']) {
					$this->_helper->FlashMessenger(array('error'=>$fileInfo['error']));
				} else if ($fileInfo['type'] != 'application/vnd.ms-excel') {
					$this->_helper->FlashMessenger(array('error'=>'Wrong file type. Must be excel (.xls)'));
				} else {
					try {
						$investigationData = $this->readExcel($fileInfo['tmp_name']);
						$investigation = Model_Investigation::insert($investigationData);
						$this->view->investigation = $investigation;
					} catch (Exception $ex) {
						$this->_helper->FlashMessenger(array('error'=>'Cannot read excel file: ' . $ex->getMessage()));
					}
				}
			}
//			$this->_helper->redirector->gotoRoute(array('action'=>'index'));
		}
	}
	
	private function readExcel($filename) {
		$data = new Spreadsheet_Excel_Reader();
		$data->read($filename);
		$sheet = $data->sheets[0];
//print_r($sheet['cells']);

		// print_r($data);

		$sites = array();
		for ($col = 2; $col <= 13; $col++) {
			if (isset($sheet['cells'][4][$col])) {
				$sites[$col] = (object)array('name'=>$sheet['cells'][4][$col], 'data'=>array());
			}
		}

		$rows = array(
			'water_width'=>5,
			'wetted_perimeter'=>6,
			'gradient'=>7,
			'depth'=>range(8,12),
			'flowrate'=>range(14,16),
			'bedload_length'=>range(24, 33),
			'roundness'=>range(35,40)
		);
		$failures = array();
		foreach ($rows as $type=>$rows) {
			if (!is_array($rows)) {
				$rows = array($rows);
			}
			foreach ($sites as $col=>$siteData) {
				$siteData->data[$type] = array();
				foreach ($rows as $row) {
					if (isset($sheet['cells'][$row][$col])) {
						$siteData->data[$type][] = $sheet['cells'][$row][$col];
//				echo "<td>$row, $col: \"".$sheet['cells'][$row][$col]."\"</td>";
					} else {
						$siteData->data[$type][] = '?';
//				$failures[] = array($row, $col);
					}
				}
			}
		}
		$investigation = new stdClass();
		$investigation->date = time();
		$investigation->schoolName = "Acland Burghley";
		$investigation->centre = "Slapton";
		$investigation->siteInvestigations = $sites;

		return $investigation;
	}
}

