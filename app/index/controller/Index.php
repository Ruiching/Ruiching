<?php
declare (strict_types = 1);

namespace app\index\controller;

use app\BaseController;
use think\Request;

class Index extends BaseController
{
    public function index()
    {
        return 'hello world';
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
