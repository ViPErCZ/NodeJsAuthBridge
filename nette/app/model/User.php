<?php
/**
 * Created by PhpStorm.
 * User: Martin
 * Date: 21.3.2015
 * Time: 21:24
 */

namespace App\Model;


use Nodejs\NodeJsAuthBridge;

class User extends \Nette\Security\User {

    /** @var NodeJsAuthBridge */
    protected $nodeBridge;

    /**
     * @return NodeJsAuthBridge
     */
    protected function createNodeBridge() {
        if ($this->nodeBridge === null) {
            $this->nodeBridge = new NodeJsAuthBridge();
            $this->nodeBridge->setPath("/nodejs/NodeJsAuthBridge");
        }
        return $this->nodeBridge;
    }

    /**
     * @return bool
     */
    final public function isLoggedIn() {
        $isLoggedIn = parent::isLoggedIn();
        $isLoggedInNode = $this->createNodeBridge()->isLoggedIn();

        return ($isLoggedIn && $isLoggedInNode);
    }

    /**
     * @param bool $clearIdentity
     */
    final public function logout($clearIdentity = false) {
        if (parent::isLoggedIn()) {
            parent::logout($clearIdentity);
        }
        if ($this->createNodeBridge()->isLoggedIn()) {
            $this->createNodeBridge()->logout();
        }
        if ($clearIdentity) {
            $this->storage->setIdentity(NULL);
        }
    }
}