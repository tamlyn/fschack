<?php

require_once 'assets/lib/excel/Excel/reader.php';

class UploadController extends BaseController {

	private $fields;

	public function init() {
		$this->tmpPath = implode(DIRECTORY_SEPARATOR, array(APPLICATION_PATH, '..', 'data', 'uploads', ''));
		@mkdir($this->tmpPath, 0777, true);

		$this->fields = array(
			'sites'            => 'Site Name',
			'water_width'      => 'Water Width',
			'wetted_perimeter' => 'Wetted Perimeter',
			'gradient_degrees' => 'Gradient (degrees)',
			'gradient_diff'    => 'Gradient (m/m)',
			'depth'            => 'Depth',
			'flowrate_speed'   => 'Flow Rate (m/s)',
			'flowrate_revs'    => 'Flow Rate (s)', // impellor duration
			'bedload_length'   => 'Bedload Length',
			'roundness'        => 'Roundness'
		);

		$this->uploadErrors = array(
			UPLOAD_ERR_OK         => "No errors",
			UPLOAD_ERR_INI_SIZE   => "Larger than upload_max_filesize",
			UPLOAD_ERR_FORM_SIZE  => "Larger than form MAX_FILE_SIZE",
			UPLOAD_ERR_PARTIAL    => "Partial upload",
			UPLOAD_ERR_NO_FILE    => "No file chosen",
			UPLOAD_ERR_NO_TMP_DIR => "No temporary directory",
			UPLOAD_ERR_CANT_WRITE => "Can't write to disk",
			UPLOAD_ERR_EXTENSION  => "File upload stopped by extension"
//			UPLOAD_ERR_EMPTY      => "File is empty"
		);
		parent::init();
	}

	public function indexAction() {
		$this->view->title = 'Import data';
		$this->view->sites = array();
		if ($this->_request->isPost()) {
			if (!isset($_FILES['file'])) {
				$this->_helper->FlashMessenger(array('error'=>'Please select an excel file'));
			} else {
				$fileInfo = $_FILES['file'];
				if ($fileInfo['error']) {
					$this->_helper->FlashMessenger(array('error'=>$this->uploadErrors[$fileInfo['error']]));
				} else if (substr($fileInfo['name'], -4) != '.xls') {
					$this->_helper->FlashMessenger(array('error'=>'Wrong file type. Must be excel (.xls)'));
				} else {
					try {
						$this->saveTempFile($fileInfo);
						$this->_helper->redirector->gotoRoute(array('action'=>'import'));
					} catch (Exception $ex) {
						$this->_helper->FlashMessenger(array('error'=>'Cannot read excel file: ' . $ex->getMessage()));
					}
				}
			}
			$this->_helper->redirector->gotoRoute(array('action'=>'index'));
		}
	}

	private function getUploadedFile() {
		if ($handle = opendir($this->tmpPath)) {
			while (false !== ($entry = readdir($handle))) {
				if (substr($entry, strlen($entry)-4) == '.xls') {
					return $this->tmpPath . $entry;
				}
			}
		}
		return null;
	}

	private function showMissingFieldsForm($sheetData) {
		$guesses = array();
		foreach ($sheetData->rows as $i=>$row) {
			$toCompare = strtolower($row);
			$guess = null;
			if (strpos($toCompare, 'mean') === false && strpos($toCompare, 'mode') === false && strpos($toCompare, 'average') === false && count($sheetData->examples[$i]) > 0) {
				if (strpos($toCompare, 'depth') !== false) {
					$guess = 'depth';
				} else if (strpos($toCompare, 'width') !== false) {
					$guess = 'water_width';
				} else if (strpos($toCompare, 'wetted') !== false) {
					$guess = 'wetted_perimeter';
				} else if (strpos($toCompare, 'gradient') !== false) {
					$guess = (strpos($toCompare, 'm') === false ? 'gradient_degrees' : 'gradient_diff');
				} else if (strpos($toCompare, 'flowrate') !== false || strpos($toCompare, 'flow rate') !== false || strpos($toCompare, 'velocity') !== false) {
					$guess = (strpos($toCompare, 'revs') === false ? 'flowrate_speed' : 'flowrate_revs');
				} else if (strpos($toCompare, 'bedload') !== false) {
					$guess = 'bedload_length';
				} else if (strpos($toCompare, 'angular') !== false || strpos($toCompare, 'round') !== false) {
					$guess = 'roundness';
				}
			}
			$guesses[$i] = $guess;
		}
		$this->view->title = 'Import data - file format';
		$this->view->sheets = $this->_request->sheets;
		$this->view->todo = $sheetData;
		$this->view->enteredFields = $guesses;
		$this->view->fields = array_merge(array('ignore'=>'[ignore]'), $this->fields);
	}

	public function importAction() {
		$this->view->title = 'Import data - file info';
		$this->view->uploadedFile = $this->getUploadedFile();

		if (!$this->view->uploadedFile) {
			$this->_helper->FlashMessenger(array('error'=>'No uploaded file found'));
			$this->_helper->redirector->gotoRoute(array('action'=>'index'));
		}

		$db = Zend_Registry::get('db');

		$sheets = array();
		$spreadsheet = new Spreadsheet_Excel_Reader();
		$spreadsheet->read($this->view->uploadedFile);
		foreach ($spreadsheet->boundsheets as $sheet) {
			$sheets[] = $sheet;
		}
		$this->view->sheets = $sheets;
		$this->view->date = $this->_request->date;
		$this->view->school = $this->_request->school;
		$this->view->centre = $this->_request->centre;
		$this->view->selectedSheets = $this->_request->sheets ?: array();

		if (!$this->_request->phase) {
			// this is the first time
			$this->_helper->viewRenderer('sheets');
		} else {
			// tried once, but insufficient data input
			$errors = array();
			if (!$this->view->date || !preg_match('/\d{2}-\d{2}-\d{4}/', $this->view->date)) {
				$errors[] = 'Please enter a valid date';
			}
			if (!$this->view->school) {
				$errors[] = 'Please enter a school';
			}
			if (!$this->view->centre) {
				$errors[] = 'Please enter a centre';
			}
			if (!$this->view->selectedSheets) {
				$errors[] = 'Please choose at least one worksheet';
			}

			if ($errors) {
				foreach ($errors as $message) {
					$this->_helper->FlashMessenger(array('error'=>$message));
				}
				$this->_helper->viewRenderer('sheets');
				return;
			}

			/*
			 * all good. Now ensure we can understand the format for each sheet
			 */

			// if the user has defined an unknown format, save it now
			if ($this->_request->fields) {
				$toSave = array();
				foreach ($this->fields as $field=>$label) {
					$toSave[$field] = array();
				}
				foreach ($this->_request->fields as $row=>$field) {
					if (array_key_exists($field, $toSave)) {
						$toSave[$field][] = $row;
					}
				}
				if (count($toSave['sites']) != 1) {
					$this->view->enteredFields = $this->_request->fields;
					$this->_helper->FlashMessenger(array('error'=>'One of the rows must be for site names'));
				} else {
					$statement = $db->prepare('REPLACE INTO file_formats (hash, fields) VALUES (:hash, :fields)');
					$statement->execute(array(':hash'=>$this->_request->hash, ':fields'=>json_encode($toSave)));
				}
			}

			// fetch each sheet signature, and the corresponding fields
			$fieldsQuery = $db->prepare('SELECT fields FROM file_formats WHERE hash = :hash');
			$sheets = array();
			foreach ($this->_request->sheets as $sheetIndex) {
				$sheet = $spreadsheet->sheets[$sheetIndex];
				$cellValues = array();
				$examples = array();
				for ($row=3; $row<=$sheet['numRows']; $row++) {
					$cellValues[$row] = (isset($sheet['cells'][$row][1]) ? $sheet['cells'][$row][1] : '');
					$examples[$row] = array();
					for ($col = 2; $col<=$sheet['numCols']; $col++) {
						if (isset($sheet['cells'][$row][$col])) {
							$examples[$row][] = $sheet['cells'][$row][$col];
							if (count($examples[$row]) > 3) break;
						}
					}
				}
				$hash = md5(implode(',', $cellValues));
				if (!array_key_exists($hash, $sheets)) {
					$fieldsQuery->execute(array(':hash'=>$hash));
					$fields = $fieldsQuery->fetch(PDO::FETCH_COLUMN);
					$sheets[$hash] = (object)array('rows'=>$cellValues, 'examples'=>$examples, 'hash'=>$hash, 'sheetIndexes'=>array(), 'fields'=>$fields);
					if (!$fields) {
						$this->showMissingFieldsForm($sheets[$hash]);
						return;
					}
				}
				$sheets[$hash]->sheetIndexes[] = $sheetIndex;
			}

			// save the data from each sheet, using the fields for its signature
			$allInvestigations = array();
			foreach ($sheets as $hash=>$sheetData) {
				$fields = (array)json_decode($fields);
				foreach ($sheetData->sheetIndexes as $sheetIndex) {
					$investigation = $this->readInvestigation($spreadsheet, $fields, $sheetIndex);
					if (count($investigation->siteInvestigations) > 0) {
						$investigation->date = $this->view->date;
						$investigation->school = $this->view->school;
						$investigation->centre = $this->view->centre;
						Model_Investigation::insert($investigation);
						$allInvestigations[] = $investigation;
					}
				}
			}

			$this->emptyTempDir();

			$this->_helper->FlashMessenger(array('info'=>'All done! Imported ' . count($allInvestigations) . ' investigations'));
			$this->_helper->redirector->gotoRoute(array('action'=>'index'));
		}
	}

	private function emptyTempDir() {
		if ($handle = opendir($this->tmpPath)) {
			while (false !== ($entry = readdir($handle))) {
				if (!in_array($entry, array('.', '..'))) {
					unlink($this->tmpPath . $entry);
				}
			}
		}
	}

	private function saveTempFile($fileInfo) {
		$this->emptyTempDir();
		$target = $this->tmpPath . $fileInfo['name'];
		if (!move_uploaded_file($fileInfo['tmp_name'], $target)) {
			$this->_helper->FlashMessenger(array('error'=>'Upload error'));
			$this->_helper->redirector->gotoRoute(array('action'=>'index'));
		}
		return $target;
	}

	private function readInvestigation($spreadsheet, $fields, $sheetIndex) {
		$sitesRow = $fields['sites'][0];
		unset($fields['sites']);

		$sheetName = $spreadsheet->boundsheets[$sheetIndex]['name'];
		$sheet = $spreadsheet->sheets[$sheetIndex];

		// for all cells in the site names row, create an empty object to hold the measurements
		$sites = array();
		for ($col = 2; $col <= $sheet['numCols']; $col++) {
			if (isset($sheet['cells'][$sitesRow][$col])) {
				$siteName = $sheet['cells'][$sitesRow][$col];
				if (strpos(strtolower($siteName), 'average') === false) {
					$sites[$col] = (object)array('site_name'=>$siteName, 'measurements'=>array());
				}
			}
		}

		// populate the measurements
		foreach ($sites as $col=>$siteData) {
			foreach ($fields as $type=>$rowIndexes) {
				$siteData->measurements[$type] = array();
				foreach ($rowIndexes as $rowIndex) {
					$siteData->measurements[$type][] = (isset($sheet['cells'][$rowIndex][$col]) ? $sheet['cells'][$rowIndex][$col] : '');
				}
			}
		}

		// remove any sites with no data
		foreach ($sites as $col=>$siteData) {
			if (empty($siteData->measurements['water_width']) || empty($siteData->measurements['water_width'][0])) {
				unset($sites[$col]);
			}
		}

		return (object)array('name'=>$sheetName, 'siteInvestigations'=>array_values($sites));
	}
}

