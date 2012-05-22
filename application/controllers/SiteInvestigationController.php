<?php

class SiteInvestigationController extends BaseController
{
	public function deleteAction() {
		$routeArgs = array('controller'=>'index', 'action'=>'index', 'id'=>null, 'source'=>null);
		if ($this->_request->isPost()) {
			$siteInvestigation = Model_SiteInvestigation::fetchById($this->_request->id);
			if ($siteInvestigation) {
				switch ($this->_request->source) {
					case 'investigation':
						$routeArgs['action'] = 'overview';
						$routeArgs['controller'] = 'investigation';
						$routeArgs['id'] = $siteInvestigation->investigationId;
						break;
					case 'site':
						$routeArgs['action'] = 'overview';
						$routeArgs['controller'] = 'site';
						$routeArgs['id'] = $siteInvestigation->siteId;
						break;
				}
				$siteInvestigation->delete();
				$this->_helper->FlashMessenger(array('info'=>'Site investigation deleted'));
			}
		}
		$this->_helper->redirector->gotoRoute($routeArgs);
	}
}
