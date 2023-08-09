<?php

namespace app\admin\repository;


use app\repository\BaseRepository;

class HandleLogRepository extends BaseRepository
{
    public function getAdmins()
    {
        return $this->adminModel->field('id, nickname')->order('id asc')->select();
    }

    public function paginate($condition = [], $param = [])
    {
        $paginateParam = [];
        if (isset($param['admin_id']) && is_numeric($param['admin_id']) && $param['admin_id'] != 0) {
            $condition['admin_id'] = $param['admin_id'];
            $paginateParam['query']['admin_id'] = $param['admin_id'];
        }
        if (isset($param['keyword']) && $param['keyword']) {
            $condition['handle'] = ['like', "%{$param['keyword']}%"];
            $paginateParam['query']['keyword'] = $param['keyword'];
        }
        $list = $this->handleLogModel->where($condition)->order('id desc')->paginate($paginateParam);
        $adminIds = [];
        foreach ($list as $item) {
            $adminIds[] = $item->admin_id;
        }
        $adminIds = array_unique($adminIds);
        $admins = $this->adminModel->where('id', 'in', $adminIds)->select();
        $id2admin = [];
        foreach ($admins as $admin) {
            $id2admin[$admin->id] = $admin->nickname;
        }
        foreach ($list as $item) {
            $item->admin = '已删除管理员';
            if (isset($id2admin[$item->admin_id])) {
                $item->admin = $id2admin[$item->admin_id];
            }
        }
        return $list;
    }
}