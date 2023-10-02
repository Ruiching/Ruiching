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

    public function evolve(Request $request)
    {
        if (Request::isPost()){
            $validate = Validate::rule([
                'event_id|关联事件ID' => 'require',
                'theme|演进主题' => 'require',
            ]);
            if (!$validate->check($request::param())) {
                return error($validate->getError());
            }
            $lists = $this->repository->getEvolveList($request::param());
            if (empty($lists)) {
                return error($this->repository->getErrorMsg(), $this->repository->getErrorCode());
            }
            return success('演进关系', $lists);
        }else{
            return error('非法请求');
        }
    }

}
