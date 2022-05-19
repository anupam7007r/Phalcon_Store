<?php

namespace App\Listeners;

use Phalcon\Events\Event;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class notificationListeners
{
    public function afterSend($e)
    {
        $postdata = $e->getData();
        // die($postdata);
        $orders = new Orders();
        $settings = Settings::find();
        if ($settings[0]->default_zipcode && $postdata->zipcode == '') {
            $postdata['zipcode'] = $settings[0]->default_zipcode;
        }
        return $postdata;
    }
    public function beforeSend($e)
    {
        $postdata = $e->getData();
        // $proDucts = Products::find();
        $settings = Settings::find();

        if ($settings[0]->title_optimization == 'With Tags') {
            $postdata['product_name'] = $postdata['product_name'] . "+" . $postdata['tags'];
        }
        if ($postdata->price == '') {
            $postdata['price'] = $settings[0]->default_price;
        }
        if ($postdata->stock == '') {
            $postdata['stock'] = $settings[0]->default_stock;
        }

        return $postdata;
    }
    public function beforeHandleRequest(Event $event, \Phalcon\Mvc\Application $application)
    {
        $aclFile = APP_PATH . '/security/acl.cache';
        if (true === is_file($aclFile)) {
            $acl = unserialize(file_get_contents($aclFile));
        }
        // else{
        //     echo "ACL not found";
        //     die;
        // }

        $bearer = $application->request->get("bearer");
        // if ($bearer) {
        //     try {

        //         $parser = new Parser();
        //         $tokenObject = $parser->parse($bearer);
        //         $now = new \DateTimeImmutable();
        //         $expires = $now->getTimestamp();
        //         // $expires = $now->modify('+1 day')->getTimestamp();
        //         $validator = new Validator($tokenObject, 100);
        //         $validator->validateExpiration($expires);
        //         // echo 'validated';
        //         // die;
        //         $claims = $tokenObject->getClaims()->getPayLoad();
        //         $role = $claims['sub'];
        //         $controller$bearer = $application->request->get("bearer");
        if ($bearer) {
            try {
                $key = "example_key";
                $decoded = JWT::decode($bearer, new Key($key, 'HS256'));
                $role = $decoded->role;
                $controller = $application->router->getControllerName();
                $action = $application->router->getActionName();
                
                if (!$role || true !== $acl->isAllowed($role, $controller, $action)) {
                    echo "access denied";
                    die();
                } 
            } catch (\Exception $e) {
                echo $e->getMessage();
                die;
            }
        } else {
            echo "Token not provided";
            die;
        }
    }
}









        // $acl = unserialize(
            //     file_get_contents($aclFile)
            // );

            // $role = $application->request->getQuery('role');
            // $controller = $application->router->getControllerName();
            // $action = $application->router->getActionName();
            // if (!$role || true !== $acl->isAllowed($role, $controller, $action)) {
            //     echo "Access denied :(";
            //     die();
            // } else {
                // echo "we don't find any acl list try after somtiome";
            // }
        // }
    // }
// }
