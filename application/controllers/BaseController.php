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

}
	
?>