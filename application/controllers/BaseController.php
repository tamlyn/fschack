<?php


class BaseController extends Zend_Controller_Action {

	protected $publicActions = array();

	public function preDispatch() {

	}

	public function init() {
		$this->config = Zend_Registry::get('config');

		//set up navigation
		$navConfig = new Zend_Config_Yaml(APPLICATION_PATH . '/configs/navigation.yaml');
		$navigation = new Zend_Navigation($navConfig);
		$this->view->navigation($navigation);

		// provide a default page title
		$this->view->title = ucfirst($this->_request->controller) . ' > ' . ucfirst($this->_request->action);

	}

	protected function validateData($item, $type = null, $action = null) {
		if (!$item) {
			$this->dataNotFound($type);
		}
	}

	// flashes a '[controller name] not found' message, then redirects to the index page
	protected function dataNotFound($type = null)
	{
		if (!$type) {
			$type = $this->_request->getControllerName();
		}
		$this->_helper->FlashMessenger(array('error' => $type . ' not found'));
		$this->_helper->redirector->gotoSimple('');
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

}
	
?>