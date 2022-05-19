<?php

use Phalcon\Mvc\Controller;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;

class SecureController extends Controller
{
    public function initializeAction()
    {
        $aclFile = APP_PATH . '/security/acl.cache';
        if (true !== is_file($aclFile)) {
            $acl = new Memory();
            $ACL = Acl::find();
            foreach ($ACL as $k => $v) {
                $acl->addRole($v->role);
                $acl->addComponent(
                    $v->selectController,
                    [
                        $v->selectAction
                    ]
                );
                $acl->allow($v->role,  $v->selectController, $v->selectAction);
            }
            file_put_contents($aclFile, serialize($acl));
        } else {
            $acl = unserialize(file_get_contents($aclFile));
        }
    }
    public function indexAction()
    {
    }
    public function addroleAction()
    {
        $aclFile = APP_PATH . '/security/acl.cache';
        if (true !== is_file($aclFile)) {
            $acl = new Memory();
        } else {
            $acl = unserialize(file_get_contents($aclFile));
            $this->view->roles = $acl->getRoles() ?? [];
            //die(print_r($acl->getRoles()));
            // $this->view->roles = $acl->getRoles() ?? [];
            if ($this->request->isPost()) {
                $postdata = $this->request->getpost();
                if ($postdata['role'] == '') {
                    $this->view->msg = '*Please enter a valid role !!';
                } else {
                    $ACL = new Acl();
                    $ACL->assign(
                        $postdata,
                        [
                            'role',

                        ]
                    );
                    $ACL->controller = null;
                    $ACL->action = null;
                    $success = $ACL->save();
                    $acl->addRole(new Role($postdata['role']));
                    file_put_contents($aclFile, serialize($acl));
                    if ($success) {
                        $this->view->msg = '*Role added successfully !!';
                        $this->view->success = $success;
                    } else {
                        $this->view->msg = '*Not Saved !!';
                    }
                    // $this->view->roles = $acl->getRoles() ?? [];
                }
            }
        }
    }
    public function addcomponentAction()
    {
        // return 'add component';
        // $this->view->roles = Acl::find();

        $aclFile = APP_PATH . '/security/acl.cache';
        if (true !== is_file($aclFile)) {
            $acl = new Memory();
        } else {
            $acl = unserialize(file_get_contents($aclFile));
            $this->view->roles = $acl->getRoles() ?? [];
            // $this->view->roles = $acl->getComponents() ?? [];
            //die(print_r($acl->getRoles()));
            // $this->view->roles = $acl->getRoles() ?? [];
            if ($this->request->isPost()) {
                $postdata = $this->request->getpost();
                if ($postdata['controller'] == '' || $postdata['action'] == '') {
                    $this->view->message = '*Please enter all fields!!';
                } else {
                    $ACL = new Acl();
                    $ACL->assign(
                        $postdata,
                        [
                            'role',
                            'controller',
                            'action'

                        ]
                    );

                    $success = $ACL->save();
                    $acl->addComponent(
                        $postdata["controller"],
                        [
                            $postdata["action"]
                        ]
                    );
                    file_put_contents($aclFile, serialize($acl));
                    if ($success) {
                        $this->view->message = '*Component added successfully !!';
                        $this->view->success = $success;
                    } else {
                        $this->view->message = '*Components not Added !!';
                    }
                    // $this->view->roles = $acl->getRoles() ?? [];
                }
            }
        }
    }
    public function allowcomponentAction()
    {
        $aclFile = APP_PATH . '/security/acl.cache';
        if (true !== is_file($aclFile)) {
            $acl = new Memory();
        } else {
            $acl = unserialize(file_get_contents($aclFile));
            $this->view->roles = $acl->getRoles() ?? [];
            $this->view->components = $acl->getComponents() ?? [];
            if ($this->request->isPost()) {
                $postdata = $this->request->getpost();
                if ($postdata['role'] == '0' || $postdata['selectController'] == '0' || $postdata['selectAction'] == '0') {
                    $this->view->error = '*Please enter all fields!!';
                } else {
                    $ACL = new Acl();
                    $ACL->assign(
                        $postdata,
                        [
                            'role',
                            'selectController',
                            'selectAction'

                        ]
                    );

                    $success = $ACL->save();
                    $acl->addComponent(
                        $postdata["selectController"],
                        [
                            $postdata["selectAction"]
                        ]
                    );
                    file_put_contents($aclFile, serialize($acl));
                    if ($success) {
                        $this->view->error = '*Permissions Granted !!';
                        $this->view->success = $success;
                    } else {
                        $this->view->error = '*Couldn\'t Grant Permissions!!';
                    }
                }
            }
        }
    }
}
