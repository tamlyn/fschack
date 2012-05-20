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

}

