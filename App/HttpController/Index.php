<?php

namespace App\HttpController;


use Base\BaseController;


class Index extends BaseController
{

    function index()
    {
        $this->errorJson('缺省参数', 404);
    }
}