<?php

use Phalcon\Mvc\Controller;


class ProductController extends Controller
{

    public function indexAction()
    {
    }

    public function addAction()
    {
        $products = new Products();
        if ($this->request->isPost()) {
            $postdata = $this->request->getPost();
            $products->assign(
                $postdata,
                [
                    'product_name',
                    'product_description',
                    'tags',
                    'price',
                    'stock'
                ]
            );
            if (
                empty($postdata['product_name']) || empty($postdata['product_description']) || empty($postdata['tags'])
            ) {
                $this->view->msg = "*Product Name or Description or Tags cannot be empty!!";
            } else {
                $success = $products->save();
                if ($success) {
                    $this->view->success = $success;
                    $this->view->msg = "*Order added Successfully!!";
                }
                if (empty($postdata['price']) && empty($postdata['stock'])) {
                    $eventmanager = $this->di->get('eventManager');
                    $newpostdata = $eventmanager->fire('notifications:beforeSend', $this, $postdata);
                    $products->assign(
                        $newpostdata,
                        [
                            'product_name',
                            'product_description',
                            'tags',
                            'price',
                            'stock'
                        ]
                    );
                    $success = $products->save();
                    // print_r($products->getMessages());
                    // die;
                    $this->view->success = $success;
                    if ($success) {
                        $this->view->msg = "*Product added Successfully!!";
                    }
                }
            }
            // $success = $products->save();
        }
    }
    public function listAction()
    {
        $products = new Products();
        $this->view->products = Products::find();
    }
}
