<?php

use Phalcon\Mvc\Controller;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Escaper;
class UsersController extends Controller
{
    public function indexAction()
    {
        //         $obj = new App\Component\Locale();
        //         $locale = $this->request->get('locale');
        //         $cacheLang=$obj->getTranslator();
        //         // if(!$this->cache->has($locale)){

        //             $this->cache->set($locale, $cacheLang);

        //         // }
        //         echo $this->cache->get($locale);
        // die;
        if ($this->request->ispost()) {

            $postdata = $this->request->getPost();
            $adduser = new Users();
            $this->view->adduser = Users::find();

            $postdata = $this->request->getPost();
            $adduser->assign(
                $postdata,
                [
                    'username',
                    'email',
                    'password',
                    'role'
                ]
            );

            if (
                empty($postdata['username']) || empty($postdata['email']) ||
                empty($postdata['password']) || $postdata['role'] == "0"
            ) {
                $this->view->adderror = $this->locale->_('*Please fill all fields!!') . '<br>';
                // $this->response->redirect('/adduser');
            } else {
                $key = "example_key";
                $payload = array(
                    "iss" => "http://example.org",
                    "aud" => "http://example.com",
                    "iat" => 1356999524,
                    "nbf" => 1357000000,
                    "role" => $postdata['role']
                );
                $jwt = JWT::encode($payload, $key, 'HS256');
                $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

                $escaper = new Escaper();
                $users = new Users();
                $data = $this->request->getPost();
                $myescaper = new \App\Components\myescaper;
                $sanitizedata = $myescaper->sanitize($data);
                $adduser->assign(
                    $sanitizedata,
                    [
                        'username',
                        'email',
                        'password',
                        'role'
                    ]
                );
                $adduser->token = $jwt;

                $success = $adduser->save();
                if ($success) {
                    $this->view->success = $success;
                    $this->view->adderror = $this->locale->_('*User Added Successfully!!') . '<br>';
                    // $this->view->token = $token;
                }
            }
        }
    }
}
