<?php

class SiteInvestigationController extends BaseController
{
	public function deleteAction() {
		$routeArgs = array('controller'=>'investigation', 'action'=>'index', 'id'=>null);
		if ($this->_request->isPost()) {
			$siteInvestigation = Model_SiteInvestigation::fetchById($this->_request->id);
			if ($siteInvestigation) {
				$routeArgs['action'] = 'overview';
				$routeArgs['id'] = $siteInvestigation->investigationId;
				$siteInvestigation->delete();
				$this->_helper->FlashMessenger(array('info'=>'Site investigation deleted'));
			}
		}
		$this->_helper->redirector->gotoRoute($routeArgs);
	}
}
