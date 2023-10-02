<?php


namespace app\api\repository;

use app\repository\BaseRepository;
use app\repository\CommonTrait;
use app\Request;
use think\facade\Env;

class IndexRepository extends BaseRepository
{
    use CommonTrait;

    public function getAllTime($params)
    {
        $query = $this->eventModel->order('timestamp desc');
        if (isset($params['start_time']) && !empty($params['start_time'])) {
            $query = $query->where('timestamp', '>=', $params['start_time']);
        }
        if (isset($params['end_time']) && !empty($params['end_time'])) {
            $query = $query->where('timestamp', '<=', $params['end_time']);
        }
        return $query->field('time, timestamp')->select();
    }

    public function getAllFields($params)
    {
        $query = $this->fieldModel->order('id desc');
        $field = 'level_0_name';
        if (isset($params['level']) && $params['level'] == 2) {
            $field = 'level_1_name';
        }
        if (isset($params['level']) && $params['level'] == 3) {
            $field = 'level_2_name';
        }
        if (isset($params['level_0']) && !empty($params['level_0'])) {
            $query = $query->where('level_0_name', $params['level_0']);
        }
        if (isset($params['level_1']) && !empty($params['level_1'])) {
            $query = $query->where('level_1_name', $params['level_1']);
        }
        return $query->group($field)->column($field);
    }

    public function getEventList($params)
    {
        $events = [];
        $query = $this->eventModel->order('timestamp desc');
        if (isset($params['start_time']) && !empty($params['start_time'])) {
            $query = $query->where('timestamp', '>=', $params['start_time']);
        }
        if (isset($params['end_time']) && !empty($params['end_time'])) {
            $query = $query->where('timestamp', '<=', $params['end_time']);
        }
        $level = empty($params['field_level']) ? 1 : $params['field_level'];
        if (isset($params['field']) && !empty($params['field'])) {
            $eventIds = [];
            if ($level == 1) {
                $eventIds = $this->eventFieldModel->where('level_0_name', $params['field'])->column('event_id');
            }
            if ($level == 2) {
                $eventIds = $this->eventFieldModel->where('level_1_name', $params['field'])->column('event_id');
            }
            if ($level == 3) {
                $eventIds = $this->eventFieldModel->where('level_2_name', $params['field'])->column('event_id');
            }
            if (!empty($eventIds)) {
                $query = $query->whereIn('event_id', $eventIds);
            }
        }
        $eventIds = $query->column('event_id');
        $list = $query->select();
        if (!empty($list)) {
            foreach ($list as $item) {
                //获取下一跳的事件ID
                $nextEventId = $this->_getNextEvent($item['event_id'], $eventIds);
                //查找是否存在于演进主题中
                $evolveInfo = $this->eventEvolveThemeModel->where('event_id', $item['event_id'])->find();
                $eventItem = [
                    'event_id' => $item['event_id'],
                    'time' => $item['time'],
                    'name' => $item['name'],
                    'next_event_id' => empty($nextEventId) ? "" : $nextEventId,
                    'evolve_info' => [
                        'has' => empty($evolveInfo) ? false : true,
                        'theme' => empty($evolveInfo) ? "" : $evolveInfo['theme'],
                    ],
                ];
                $events[] = $eventItem;
            }
        }
        return $events;
    }

    public function getEvolveList($params)
    {
        $events = [];
        $eventIds = $this->eventEvolveThemeModel->where('theme', $params['theme'])->column('event_id');
        $list = $this->eventModel->whereIn('event_id', $eventIds)->order('timestamp desc')->select();
        if (!empty($list)) {
            foreach ($list as $item) {
                //获取下一跳的事件ID
                $nextEventId = $this->_getNextEvent($item['event_id'], $eventIds);
                $eventItem = [
                    'event_id' => $item['event_id'],
                    'time' => $item['time'],
                    'name' => $item['name'],
                    'next_event_id' => empty($nextEventId) ? "" : $nextEventId,
                ];
                $events[] = $eventItem;
            }
        }
        return $events;
    }
}