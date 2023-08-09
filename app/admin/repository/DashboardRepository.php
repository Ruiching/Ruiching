<?php

namespace app\admin\repository;

use app\repository\BaseRepository;
use think\facade\Db;

class DashboardRepository extends BaseRepository
{
    public function getStatusData($params)
    {
        $condition = $deviceIds = [];
        $startTime = date('Y-m-d', time()) . " 00:00:00";
        $endTime = date('Y-m-d', time()) . " 23:59:59";
        if (!empty($params['start_time']) && isset($params['start_time'])) {
            $startTime = $params['start_time'] . " 00:00:00";
        }
        if (!empty($params['end_time']) && isset($params['end_time'])) {
            $endTime = $params['end_time'] . " 23:59:59";
        }
        if (!empty($params['activity_id']) && isset($params['activity_id'])) {
            $deviceIds = $this->deviceModel->where('activity_id', $params['activity_id'])->column('id');
        }
        if (!empty($params['device_id']) && isset($params['device_id'])) {
            $deviceIds = [$params['device_id']];
        }
        if (!empty($deviceIds)) {
            $condition[] = ['device_id', 'in', $deviceIds];
        }

        $lists = [];
        $lists['all']['device_start_qr'] = 0;
        $lists['all']['device_start_num'] = 0;
        $lists['all']['device_start_people'] = 0;
        $lists['all']['photo_get_qr'] = 0;
        $lists['all']['photo_get_num'] = 0;
        $lists['all']['photo_get_people'] = 0;
        $lists['all']['register_people'] = 0;

        for ($time = strtotime($startTime); $time <= strtotime($endTime); $time = $time + 86400) {
            $item = [];
            $dayStartTime = date('Y-m-d', $time) . " 00:00:00";
            $dayEndTime = date('Y-m-d', $time) . " 23:59:59";
            $item['day'] = date('Y-m-d', strtotime($dayStartTime));

            $item['device_start_qr'] = $this->deviceLogModel
                ->where($condition)
                ->whereTime('created_at', 'between', [$dayStartTime, $dayEndTime])
                ->count();

            $item['device_start_num'] = $this->deviceLogModel
                ->where($condition)
                ->whereTime('created_at', 'between', [$dayStartTime, $dayEndTime])
                ->where('user_id', '>',0)
                ->count();

            $item['device_start_people'] = $this->deviceLogModel
                ->where($condition)
                ->whereTime('created_at', 'between', [$dayStartTime, $dayEndTime])
                ->where('user_id', '>',0)
                ->group('user_id')
                ->count();

            $item['photo_get_qr'] = $this->deviceMediaModel
                ->where($condition)
                ->whereTime('created_at', 'between', [$dayStartTime, $dayEndTime])
                ->count();

            $item['photo_get_num'] = $this->deviceMediaModel
                ->where($condition)
                ->whereTime('created_at', 'between', [$dayStartTime, $dayEndTime])
                ->where('user_id', '>',0)
                ->count();

            $item['photo_get_people'] = $this->deviceMediaModel
                ->where($condition)
                ->whereTime('created_at', 'between', [$dayStartTime, $dayEndTime])
                ->where('user_id', '>',0)
                ->group('user_id')
                ->count();

            $item['register_people'] = $this->activityUserModel
                ->where($condition)
                ->whereTime('created_at', 'between', [$dayStartTime, $dayEndTime])
                ->count();

            $lists['item'][] = $item;
            $lists['all']['device_start_qr'] += $item['device_start_qr'];
            $lists['all']['device_start_num'] += $item['device_start_num'];
            $lists['all']['device_start_people'] += $item['device_start_people'];
            $lists['all']['photo_get_qr'] += $item['photo_get_qr'];
            $lists['all']['photo_get_num'] += $item['photo_get_num'];
            $lists['all']['photo_get_people'] += $item['photo_get_people'];
            $lists['all']['register_people'] += $item['register_people'];

        }
        return $lists;
    }
}
