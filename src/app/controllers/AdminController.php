<?php

use Phalcon\Mvc\Controller;


class AdminController extends Controller
{
    public function indexAction()
    {
        if(!$this->session->get('email')){
            $this->response->redirect('/index/index');
        }
    }
}