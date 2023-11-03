<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\service\TokenService;
use think\App;
use think\facade\Request;
use think\facade\Validate;
use think\facade\View;

class Login extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers:*');
    }

    public function index(Request $request)
    {
        file_put_contents('php://stdout', 'Hello world');

        if($request::isPost()) {
            $validate = Validate::rule([
                'username|账号'  => 'require',
                'password|密码' => 'require',
            ]);
            if (!$validate->check($request::post())) {
                $this->error($validate->getError());
            }

            if ($request::param('username') != env('app.user_login_name')) {
                return error("账号或密码错误");
            }
            if ($request::param('password') != env('app.user_login_password')) {
                return error("账号或密码错误");
            }

            $token = TokenService::generateToken(env('app.user_login_token'));
            return success('登录成功', $token);
        }
        return error("请求错误");
    }
}
