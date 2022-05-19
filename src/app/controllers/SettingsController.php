<?php

use Phalcon\Mvc\Controller;


class SettingsController extends Controller
{
    public function indexAction()
    {
        $settings = new Settings();
        $this->view->settings = Settings::find();
        $s = Settings::find();
        if ($this->request->getpost()) {
            $settings->assign(
                $postdata = $this->request->getPost(),
                [
                    'title_optimization',
                    'default_price',
                    'default_stock',
                    'default_zipcode',

                ]
            );
            if (
                empty($postdata['title_optimization']) || empty($postdata['default_price']) || empty($postdata['default_stock']) ||
                empty($postdata['default_zipcode'])
            ) {
                $this->view->settingsmsg = "*Please fill all fields";
            } else {
                // die('hii');
                foreach ($s as $k) {
                    if ($k->id == 1) {  
                        $k->title_optimization = $postdata['title_optimization'];
                        $k->default_price = $postdata['default_price'];
                        $k->default_stock = $postdata['default_stock'];
                        $k->default_zipcode = $postdata['default_zipcode'];
                        $success = $k->save();
                        $this->view->success = $success;
                        $this->view->settingsmsg = "*Settings Saved Successfully!!";
                    }
                }
            }
        }
    }
}
