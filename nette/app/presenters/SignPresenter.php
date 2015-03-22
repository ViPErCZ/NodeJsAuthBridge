<?php

namespace App\Presenters;

use Nette,
	App\Forms\SignFormFactory;
use Nodejs\NodeJsAuthBridge;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{
	/** @var SignFormFactory @inject */
	public $factory;


	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$nodeBridge = new NodeJsAuthBridge();
		$nodeBridge->setPath("/nodejs/NodeJsAuthBridge");
		//Nette\Diagnostics\Debugger::dump($nodeBridge->isLoggedIn());

		$form = $this->factory->create();
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('Homepage:');
		};
		return $form;
	}


	public function actionOut()
	{
		$this->getUser()->logout(true);
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}

}
