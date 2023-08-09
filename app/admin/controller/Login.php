<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\repository\LoginRepository;
use app\BaseController;
use think\App;
use think\facade\Session;
use think\facade\Validate;
use think\facade\Request;
use think\facade\View;

class Login extends BaseController
{
    protected $repository;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->repository = new LoginRepository();
    }

    /**
     * @param \think\facade\Request $request
     * @return \think\response\View|void
     */
    public function index(Request $request)
    {
        if($request::isPost()) {
            $check = $request::checkToken('__token__');
            if(false === $check) {
                $this->error("令牌无效");
            }
            $validate = Validate::rule([
                'username|账号'  => 'require',
                'password|密码' => 'require',
            ]);
            if (!$validate->check($request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->adminLogin($request::post('username'), $request::post('password'));
            if (!$flag) {
                $this->error($this->repository->getErrorMsg());
            }
            $this->success("登录成功", admin_url("index/index"));
        }
        View::assign('project', env('app.project_name', '荷兰号'));
        return view('login/index');
    }

    public function logout()
    {
        Session::delete('admin');
        Session::delete('admin_id');
        $this->success('注销成功', admin_url('login/index'));
    }
}

