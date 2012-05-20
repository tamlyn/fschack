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

}
	
?>