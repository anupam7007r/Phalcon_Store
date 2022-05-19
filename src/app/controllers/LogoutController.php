<?php

use Phalcon\Mvc\Controller;


class LogoutController extends Controller
{
    public function indexAction()
    {
       
        $this->session->destroy();
        $this->response->redirect("/index/index");
    }
}
