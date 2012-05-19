<?php

class ChartController extends BaseController
{
	public function indexAction() {
		$this->view->investigations = array();//Model_Investigation::fetchAll();
	}
}

