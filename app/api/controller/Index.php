<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\api\repository\IndexRepository;
use think\App;
use think\facade\Request;
use think\facade\Validate;

class Index extends Auth
{
    protected $repository;

    public function __construct(App $app, Request $request)
    {
        parent::__construct($app, $request);
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:*');
        $this->repository = new IndexRepository();
    }

    /**
     * 获取时间轴
     * @param Request $request
     * @return array
     */
    public function times(Request $request)
    {
        $times = $this->repository->getAllTime();
        return success('时间轴', $times);
    }

    /**
     * 获取学科
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $fields = $this->repository->getAllFields();
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
            $lists = $this->repository->getEventListV3($request::param());
            return success('事件列表', $lists);
        }else{
            return error('非法请求');
        }
    }

    /**
     * 获取事件分布
     * @param Request $request
     * @return array
     */
    public function eventMap(Request $request)
    {
        $map = $this->repository->getEventMap($request::param());
        return success('事件分布', $map);
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

    /**
     * 搜索推荐事件
     * @param Request $request
     * @return array
     */
    public function recommend(Request $request)
    {
        if (Request::isPost()){
            $lists = $this->repository->getRecommendList($request::param());
            return success('事件列表', $lists);
        }else{
            return error('非法请求');
        }
    }

}
