<?php
namespace app\admin\controller;

use app\admin\repository\ConfigRepository;
use think\App;
use think\facade\Request;

class Config extends Auth
{
    protected $repository;

    public function __construct(App $app, Request $request)
    {
        parent::__construct($app, $request);
        $this->repository = new ConfigRepository();
    }

    public function base(Request $request)
    {
        if ($request::isPost()) {
            $flag = $this->repository->setBase($request::param());
            if ($flag) {
                $this->success('设置成功');
            }
            $this->error('操作失败');
        }
        $configs = $this->repository->getConfig();
        return view('', compact('configs'));
    }
}
