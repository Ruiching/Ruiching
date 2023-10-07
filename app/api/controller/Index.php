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
        header('Access-Control-Allow-Origin:*');
        $this->repository = new IndexRepository();
    }

    /**
     * 获取时间轴
     * @param Request $request
     * @return array
     */
    public function times(Request $request)
    {
        $times = $this->repository->getAllTime($request::param());
        return success('时间轴', $times);
    }

    /**
     * 获取学科
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $fields = $this->repository->getAllFields($request::param());
        return success('学科列表', $fields);
    }

    /**
     * 获取人物
     * @param Request $request
     * @return array
     */
    public function subject(Request $request)
    {
        $fields = $this->repository->getAllSubject($request::param());
        return success('人物列表', $fields);
    }

    /**
     * 获取事件
     * @param Request $request
     * @return array
     */
    public function events(Request $request)
    {
        if (Request::isPost()){
            $lists = $this->repository->getEventList($request::param());
            if (empty($lists)) {
                return error($this->repository->getErrorMsg(), $this->repository->getErrorCode());
            }
            return success('事件列表', $lists);
        }else{
            return error('非法请求');
        }
    }

    /**
     * 获取演进关系
     * @param Request $request
     * @return array
     */
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
