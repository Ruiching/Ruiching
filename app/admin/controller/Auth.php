<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\BaseController;
use app\repository\CommonTrait;
use think\App;
use think\facade\Session;
use think\facade\View;
use think\facade\Request;

class Auth extends BaseController
{
    Use CommonTrait;
    protected $admin;

    /**
     * 鉴权
     * @param App $app
     * @param Request $request
     */
    public function __construct(App $app, Request $request)
    {
        parent::__construct($app);

        $adminId = Session::get('admin_id');
        if (!$adminId) {
            $loginUrl = admin_url('login/index');
            redirect($loginUrl)->send();
            exit();
        }
        $this->admin = Session::get('admin');
        $perms = get_admin_perms();
        if (!$perms['is_super']) {
            $controller = $request::controller();
            $action = $request::action();
            $route = $controller . '/' . $action;
            $currentPerm = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $route));
            $currentPerm1 = strtolower($route);
            $userPerms = array_map('strtolower', $perms['perms']);
            if (!in_array($currentPerm, $userPerms) && !in_array($currentPerm1, $userPerms)) {
                $this->error('暂无权限');
            }
        }
        $menus = get_admin_menus();
        View::assign('menus', $menus);
        View::assign('project', env('app.project_name', '荷兰号'));
    }

}
