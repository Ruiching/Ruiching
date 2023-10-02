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

    public function times(Request $request)
    {
        $times = $this->repository->getAllTime($request::param());
        return success('时间轴', $times);
    }

    public function fields(Request $request)
    {
        $fields = $this->repository->getAllFields($request::param());
        return success('学科列表', $fields);
    }

    public function events(Request $request)
    {
        $lists = $this->repository->getEventList($request::param());
        return success('事件列表', $lists);
    }

}
