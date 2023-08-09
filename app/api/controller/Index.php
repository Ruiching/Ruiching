<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\api\repository\IndexRepository;
use app\BaseController;
use think\App;
use think\facade\Request;
use think\facade\Validate;

class Index extends BaseController
{
    protected $repository;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->repository = new IndexRepository();
    }

}
