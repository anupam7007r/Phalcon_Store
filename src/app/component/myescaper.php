<?php

namespace App\Components;

use Phalcon\Escaper;

class myescaper
{
    public function sanitize($data)
    {
        $escaper = new Escaper();
        $data = array(
            "username" => $escaper->escapeHtml($data['username']),
            "email" => $escaper->escapeHtml($data['email']),
            "password" => $escaper->escapeHtml($data['password']),
        );
        return $data;
    }
}