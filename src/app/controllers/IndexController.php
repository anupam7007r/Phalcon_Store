<?php

use Phalcon\Mvc\Controller;


class IndexController extends Controller
{
    public function indexAction()
    {
        if($this->session->get('email')){
            $this->response->redirect('/admin/index?bearer='.$this->request->get('bearer'));
        }
        if ($this->request->isPost()) {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            if (empty($email) || empty($password)) {
                $this->view->msg = "*Please fill all fields"."<br>";
            } else {
                $user = Users::findFirst(array(
                    'email = :email: and password = :password:', 'bind' => array(
                        'email' => $this->request->getPost("email"),
                        'password' => $this->request->getPost("password")
                    )
                ));
                if (!$user) {
                    $this->view->msg = "*Incorrect Credentials"."<br>";
                } else {

                    global $container;
                   
                    $this->session->set('email', $user->user_id);

                    header('location: /admin/index?bearer='.$this->request->get('bearer'));
                }
            }
        }
    }
}