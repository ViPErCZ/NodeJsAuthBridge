<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Nodejs\NodeJsAuthBridge;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$nodeBridge = new NodeJsAuthBridge();
		$nodeBridge->setPath("/nodejs/NodeJsAuthBridge");
		Nette\Diagnostics\Debugger::dump($nodeBridge->isLoggedIn());
		$this->template->anyVariable = 'any value';
	}

}
