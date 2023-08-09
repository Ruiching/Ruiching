<?php

namespace app\admin\repository;

use app\repository\BaseRepository;
use think\facade\Session;

class LoginRepository extends BaseRepository
{
    public function adminLogin($userName, $password)
    {
        $adminUser = $this->adminModel->where('username', $userName)->find();
        if ($adminUser && password_verify($password, $adminUser['password'])) {
            if (empty($user['is_seal'])) {
                Session::set('admin', $adminUser);
                Session::set('admin_id', $adminUser['id']);
                handle_log('登录成功');
                return true;
            }
            $this->errorMsg = '用户已禁用, 请联系管理员';
        } else {
            $this->errorMsg = '用户名或密码不正确';
        }
    }
}