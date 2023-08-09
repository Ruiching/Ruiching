<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\repository\PermRepository;
use think\App;
use think\facade\Request;
use think\facade\Validate;

class Perm extends Auth
{
    protected $repository;

    public function __construct(App $app, Request $request)
    {
        parent::__construct($app, $request);
        $this->repository = new PermRepository();
    }

    public function role()
    {
        $list = $this->repository->getRoles();
        return view('', compact('list'));
    }

    public function roleCreate(Request $request)
    {
        if (Request::isPost()) {
            $validate = Validate::rule([
                'nickname|角色昵称' => 'require',
                'name|角色名' => 'require',
                'description|角色简介' => 'require',
                'perms|拥有权限' => 'require|array',
            ]);
            if (!$validate->check(Request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->roleStore(Request::post());
            if ($flag) {
                $this->success('新增成功', admin_url('perm/role'));
            }
            $this->error('非法操作');
        }
        $perms = get_admin_all_menus();
        return view('role-create', compact('perms'));
    }

    public function roleUpdate(Request $request)
    {
        $id = $request::param('id');
        if (!$id) {
            $this->error('请选择角色');
        }
        $info = $this->repository->roleFind($id);
        if (!$info) {
            $this->error('角色不存在');
        }
        if ($info->is_system) {
            $this->error('系统用户无法编辑');
        }
        if (Request::isPost()) {
            $validate = Validate::rule([
                'nickname|角色昵称' => 'require',
                'name|角色名' => 'require',
                'description|角色简介' => 'require',
                'perms|拥有权限' => 'require|array',
            ]);
            if (!$validate->check(Request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->roleUpdate($id, Request::post());
            if ($flag) {
                $this->success('编辑成功', admin_url('perm/role'));
            }
            $this->error('操作失败');
        }
        $perms = get_admin_all_menus();
        $ownPerms = explode(',', $info->perms);
        return view('role-update', compact('perms', 'info', 'ownPerms'));
    }

    public function roleDestroy(Request $request)
    {
        if (Request::isPost()) {
            $id = $request::param('id');
            if (!$id) {
                $this->error('角色不存在');
            }
            $flag = $this->repository->roleDestroy($id);
            if ($flag) {
                $this->success('删除成功');
            }
            $this->error($this->repository->getErrorMsg());
        }
        $this->error('删除失败');
    }

    public function roleSingleUpdate(Request $request)
    {
        if (Request::isPost()) {
            $validate = Validate::rule([
                'id|ID' => 'require',
                'field|状态' => 'require',
                'value|状态' => 'require',
            ]);
            if (!$validate->check(Request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->roleSingleUpdate(Request::post());
            if ($flag) {
                $this->success('设置成功');
            }
            $this->error($this->repository->getErrorMsg());
        }
        $this->error('非法操作');
    }

    public function roleBatchUpdate(Request $request)
    {
        if (Request::isPost()) {
            $validate = Validate::rule([
                'ids|选中角色' => 'require|array',
            ]);
            if (!$validate->check(Request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->roleBatchUpdate(Request::post());
            if ($flag) {
                $this->success('设置成功');
            }
            $this->error($this->repository->getErrorMsg());
        }
        $this->error('非法操作');
    }

    public function roleBatchDestroy(Request $request)
    {
        if (Request::isPost()) {
            $validate = Validate::rule([
                'ids|选中角色' => 'require|array',
            ]);
            if (!$validate->check(Request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->roleBatchDestroy(Request::post());
            if ($flag) {
                $this->success('删除成功');
            }
            $this->error($this->repository->getErrorMsg());
        }
        $this->error('非法操作');
    }

    public function user(Request $request)
    {
        $list = $this->repository->getUsers();
        return view('', compact('list'));
    }

    public function userCreate(Request $request)
    {
        if (Request::isPost()) {
            $validate = Validate::rule([
                'roles|所属角色' => 'require|array',
                'nickname|用户昵称' => 'require',
                'username|用户登录名' => 'require|alphaDash|length:4,20|unique:admin',
                'password|用户密码' => 'require|min:4',
                'is_seal|状态' => 'require',
            ]);
            if (!$validate->check(Request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->userStore(Request::post());
            if ($flag) {
                $this->success('新增成功', admin_url('perm/user'));
            }
            $this->error('非法操作');
        }
        $roles = $this->repository->getAllRoles();
        return view('user-create', compact('roles'));
    }

    public function userUpdate(Request $request)
    {
        $id = $request::param('id');
        if (!$id) {
            $this->error('请选择用户');
        }
        if ($id == 1) {
            $this->error('超管不可编辑');
        }
        $info = $this->repository->userFind($id);
        if (!$info) {
            $this->error('用户不存在');
        }
        if (Request::isPost()) {
            $validate = Validate::rule([
                'roles|所属角色' => 'require|array',
                'nickname|用户昵称' => 'require',
                'password|用户密码' => 'min:4',
                'is_seal|状态' => 'require',
            ]);
            if (!$validate->check(Request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->userUpdate($id, Request::post());
            if ($flag) {
                $this->success('编辑成功', admin_url('perm/user'));
            }
            $this->error($this->repository->getErrorMsg());
        }
        $roles = $this->repository->getAllRoles();
        return view('user-update', compact('roles', 'info', 'ownPerms'));
    }

    public function userDestroy(Request $request)
    {
        if (Request::isPost()) {
            $id = $request::param('id');
            if (!$id) {
                $this->error('用户不存在');
            }
            $flag = $this->repository->userDestroy($id);
            if ($flag) {
                $this->success('删除成功');
            }
            $this->error($this->repository->getErrorMsg());
        }
        $this->error('删除失败');
    }

    public function userSingleUpdate(Request $request)
    {
        if (Request::isPost()) {
            $validate = Validate::rule([
                'id|ID' => 'require',
                'field|状态' => 'require',
                'value|状态' => 'require',
            ]);
            if (!$validate->check(Request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->userSingleUpdate(Request::post());
            if ($flag) {
                $this->success('设置成功');
            }
            $this->error($this->repository->getErrorMsg());
        }
        $this->error('非法操作');
    }

    public function userBatchUpdate(Request $request)
    {
        if (Request::isPost()) {
            $validate = Validate::rule([
                'ids|选中角色' => 'require|array',
            ]);
            if (!$validate->check(Request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->userBatchUpdate(Request::post());
            if ($flag) {
                $this->success('设置成功');
            }
            $this->error($this->repository->getErrorMsg());
        }
        $this->error('非法操作');
    }

    public function userBatchDestroy(Request $request)
    {
        if (Request::isPost()) {
            $validate = Validate::rule([
                'ids|选中角色' => 'require|array',
            ]);
            if (!$validate->check(Request::post())) {
                $this->error($validate->getError());
            }
            $flag = $this->repository->userBatchDestroy(Request::post());
            if ($flag) {
                $this->success('删除成功');
            }
            $this->error($this->repository->getErrorMsg());
        }
        $this->error('非法操作');
    }
}
