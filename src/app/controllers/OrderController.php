<?php

use Phalcon\Mvc\Controller;
// namespace 

class OrderController extends Controller
{
    public function indexAction()
    {
    }
    public function addAction()
    {
        $orders = new Orders();
        $this->view->products = Products::find();
        if ($this->request->ispost()) {
            $postdata = $this->request->getPost();
            $orders->assign(
                $postdata,
                [
                    'customer_name',
                    'customer_address',
                    'zipcode',
                    'product_id',
                    'quantity'
                ]
            );
            if (
                empty($postdata['customer_name']) || empty($postdata['customer_address']) ||
                empty($postdata['product_id']) || empty($postdata['quantity'])
            ) {
                $this->view->ordermsg = "*Please fill all fields";
            } else {
                $success = $orders->save();
                if ($success) {
                    $this->view->success = $success;
                    $this->view->ordermsg = "*Order added Successfully!!";
                }
                if ($postdata['zipcode'] == '') {
                    $eventmanager = $this->di->get('eventManager');
                    $newpostdata = $eventmanager->fire('notifications:afterSend', $this, $postdata);
                    $orders->assign(
                        $newpostdata,
                        [
                            'customer_name',
                            'customer_address',
                            'zipcode',
                            'product_id',
                            'quantity'
                        ]
                    );
                    $success = $orders->save();
                    if ($success) {
                        $this->view->ordermsg = "*Order added Successfully!!";
                        $this->view->success = $success;
                    }
                }
            }
        }
    }
    public function listAction()
    {
        $orders = new Orders();
        $this->view->orders = Orders::find();
    }
}
