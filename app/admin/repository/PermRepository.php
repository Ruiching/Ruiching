<?php

namespace app\admin\repository;

use app\repository\BaseRepository;
use think\facade\Cache;

class PermRepository extends BaseRepository
{
    public function getRoles($condition = [], $param = [])
    {
        $paginateParam = [];
        $query = $this->adminRoleModel->where($condition);
        if (isset($param['keyword']) && $param['keyword']) {
            $query = $query->whereLike('name', "%{$param['keyword']}%");
            $paginateParam['query']['keyword'] = $param['keyword'];
        }
        return $query->order('is_super desc,sort asc,id desc')->paginate($paginateParam);
    }

    public function roleStore($data)
    {
        $data['perms'] = implode(',', $data['perms']);
        $data['created_at'] = $data['updated_at'] = time();
        $flag = $this->adminRoleModel->save($data);
        if ($flag) {
            handle_log('新增角色', array_merge($data, ['id' => $this->adminRoleModel->id]));
        }
        return $flag;
    }

    public function roleFind($condition = [])
    {
        if (!is_array($condition)) {
            return $this->adminRoleModel->find($condition);
        }
        return $this->adminRoleModel->where($condition)->find();
    }

    public function roleUpdate($id, $data)
    {
        $data['perms'] = implode(',', $data['perms']);
        $data['created_at'] = $data['updated_at'] = time();
        $flag = $this->adminRoleModel->update($data, ['id' => $id]);
        if ($flag) {
            handle_log('编辑角色', array_merge($data, ['id' => $id]));
        }
        return $flag;
    }

    public function roleDestroy($id)
    {
        $entity = $this->adminRoleModel->find($id);
        if ($entity) {
            if ($entity->is_system) {
                $this->errorMsg = '系统用户不可删除';
                return false;
            }
            $hasAdmin = $this->adminRoleMapModel->where(['role_id' => $id])->count();
            if ($hasAdmin) {
                $this->errorMsg = '角色下有管理员, 无法删除';
                return false;
            }
            $entity->delete();
            handle_log('删除角色', ['id' => $id]);
            return true;
        }
        return false;
    }

    public function roleSingleUpdate($param = [])
    {
        if (!in_array($param['field'], ['is_seal']) || !in_array($param['value'], [0, 1])) {
            return false;
        }
        $role = $this->adminRoleModel->find($param['id']);
        if ($role) {
            if (!$role->is_system) {
                $field = $param['field'];
                $role->$field = $param['value'];
                $role->save();
                if ($param['value']) {
                    handle_log('禁用角色', ['id' => $param['id']]);
                } else {
                    handle_log('启用角色', ['id' => $param['id']]);
                }
                return true;
            } else {
                $this->errorMsg = '无法编辑系统角色';
            }
        }
        return false;
    }

    public function roleBatchUpdate($param = [])
    {
        $ids = $param['ids'];
        $data = [];
        $hanSys = $this->adminRoleModel->where(['id' => ['in', $ids], 'is_system' => 1])->count();
        if ($hanSys) {
            $this->errorMsg = '无法编辑系统角色';
            return false;
        }
        if (isset($param['is_seal'])) {
            $data['is_seal'] = $param['is_seal'];
        }
        $this->adminRoleModel->update($data, ['id' => ['in', $ids]]);
        if ($param['is_seal']) {
            handle_log('批量禁用角色', ['ids' => $ids]);
        } else {
            handle_log('批量启用角色', ['ids' => $ids]);
        }
        return true;
    }

    public function roleBatchDestroy($param = [])
    {
        $ids = $param['ids'];
        $hanSys = $this->adminRoleModel->where(['id' => ['in', $ids], 'is_system' => 1])->count();
        if ($hanSys) {
            $this->errorMsg = '无法删除系统角色';
            return false;
        }
        $hasAdmin = $this->adminRoleMapModel->where(['role_id' => ['in', $ids]])->count();
        if ($hasAdmin) {
            $this->errorMsg = '角色下有管理员, 无法删除';
            return false;
        }
        $this->adminRoleModel->destroy($param['ids']);
        handle_log('批量删除角色', ['ids' => $ids]);
        return true;
    }

    ###用户

    public function getAllRoles()
    {
        return $this->adminRoleModel->order('is_super desc, id desc')->select();
    }

    public function getUsers($condition = [], $param = [])
    {
        $paginateParam = [];
        $condition = array_merge($condition);
        if (isset($param['keyword']) && $param['keyword']) {
            $condition['username|nickname'] = ['like', "%{$param['keyword']}%"];
            $paginateParam['query']['keyword'] = $param['keyword'];
        }
        return $this->adminModel->where($condition)->order('id desc')->paginate($paginateParam);
    }

    public function userStore($data)
    {
        $data['password'] = password_hash($data['password'], 1);
        $data['created_at'] = $data['updated_at'] = time();
        $flag = $this->adminModel->save($data);
        if ($flag) {
            $adminId = $this->adminModel->id;
            $maps = [];
            foreach ($data['roles'] as $roleId) {
                $maps[] = [
                    'admin_id' => $adminId,
                    'role_id' => $roleId,
                ];
            }
            $this->adminRoleMapModel->insertAll($maps);
        }
        return $flag;
    }

    public function userFind($condition = [])
    {
        if (!is_array($condition)) {
            $entity = $this->adminModel->find($condition);
        } else {
            $entity = $this->adminModel->where($condition)->find();
        }
        if ($entity) {
            $roleIds = $this->adminRoleMapModel->where(['admin_id' => $entity->id])->column('role_id');
            $entity->role_ids = $roleIds;
        }
        return $entity;
    }

    public function userUpdate($id, $data)
    {
        $entity = $this->adminModel->find($id);
        if ($entity) {
            $entity->nickname = $data['nickname'];
            $entity->is_seal = $data['is_seal'];
            $entity->created_at = time();
            $entity->updated_at = time();
            if ($data['password']) {
                $entity->password = password_hash($data['password'], 1);
            }
            $entity->save();
            $this->adminRoleMapModel->where(['admin_id' => $id])->delete();
            $maps = [];
            foreach ($data['roles'] as $roleId) {
                $maps[] = [
                    'admin_id' => $id,
                    'role_id' => $roleId,
                ];
            }
            $this->adminRoleMapModel->insertAll($maps);
            Cache::delete('admin-perms:' . $id);
            return true;
        }
        $this->errorMsg = '用户不存在';
        return false;
    }

    public function userDestroy($id)
    {
        if ($id == 1) {
            $this->errorMsg = '无法删除超管';
            return false;
        }
        $entity = $this->adminModel->find($id);
        if ($entity) {
            $this->adminRoleMapModel->where(['admin_id' => $id])->delete();
            $entity->delete();
            return true;
        }
        $this->errorMsg = '用户不存在';
        return false;
    }

    public function userSingleUpdate($param = [])
    {
        if ($param['id'] == 1) {
            $this->errorMsg = '无法编辑系统用户';
            return false;
        }
        if (!in_array($param['field'], ['is_seal']) || !in_array($param['value'], [0, 1])) {
            return false;
        }
        $user = $this->adminModel->find($param['id']);
        if ($user) {
            $field = $param['field'];
            $user->$field = $param['value'];
            $user->save();
            return true;
        }
        $this->errorMsg = '用户不存在';
        return false;
    }

    public function userBatchUpdate($param = [])
    {
        $ids = $param['ids'];
        $data = [];
        if (in_array(1, $ids)) {
            $this->errorMsg = '无法编辑系统用户';
            return false;
        }
        if (isset($param['is_seal'])) {
            $data['is_seal'] = $param['is_seal'];
        }
        $this->adminModel->update($data, ['id' => ['in', $ids]]);
        return true;
    }

    public function userBatchDestroy($param = [])
    {
        $ids = $param['ids'];
        if (in_array(1, $ids)) {
            $this->errorMsg = '无法编辑系统用户';
            return false;
        }
        $this->adminRoleMapModel->where(['admin_id' => ['in', $param['ids']]])->delete();
        $this->adminModel->destroy($param['ids']);
        return true;
    }

    public function getAllUsers($condition = [])
    {
        return $this->adminModel->where($condition)->order('id desc')->select();
    }
}
