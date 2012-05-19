<?php


class BaseController extends Zend_Controller_Action {

	protected $publicActions = array();

	public function preDispatch() {

	}

	public function init() {
		$this->config = Zend_Registry::get('config');

		// provide a default page title
		$this->view->title = ucfirst($this->_request->controller) . ' > ' . ucfirst($this->_request->action);

	}

}
	
?>