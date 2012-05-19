<?php

class InvestigationController extends BaseController
{
	public function indexAction() {
		$this->view->investigations = array();//Model_Investigation::fetchAll();
	}

	public function viewAction() {
	}
}

