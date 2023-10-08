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
        $query = $this->eventModel->group('min_year')->order('min_year', 'desc');
        if (isset($params['start_time']) && !empty($params['start_time'])) {
            $query = $query->where('max_year', '<=', $params['start_time']);
        }
        if (isset($params['end_time']) && !empty($params['end_time'])) {
            $query = $query->where('min_year', '>=', $params['end_time']);
        }
        $times = [];
        $lists = $query->field('min_year')->select();
        if (!empty($lists)) {
            foreach ($lists as $item) {
                $times[] = [
                    'time' => $item['min_year'] . '年',
                    'year' => $item['min_year'],
                ];
            }
        }
        return $times;
    }

    public function getAllFields($params)
    {
        $query = $this->eventFieldModel;

        //学科字段
        $field = 'level_0_name';
        if (isset($params['level']) && $params['level'] == 2) {
            $field = 'level_1_name';
        }
        if (isset($params['level']) && $params['level'] == 3) {
            $field = 'level_2_name';
        }

        //上级学科筛选
        if (isset($params['level_1']) && !empty($params['level_1'])) {
            $query = $query->where('level_0_name', $params['level_1']);
        }
        if (isset($params['level_2']) && !empty($params['level_2'])) {
            $query = $query->where('level_1_name', $params['level_2']);
        }

        //时间筛选
        if ( (isset($params['start_time']) && !empty($params['start_time'])) || (isset($params['end_time']) && !empty($params['end_time'])) ) {
            $eventQuery = $this->eventModel;
            if (isset($params['start_time']) && !empty($params['start_time'])) {
                $eventQuery = $eventQuery->where('max_year', '<=', $params['start_time']);
            }
            if (isset($params['end_time']) && !empty($params['end_time'])) {
                $eventQuery = $eventQuery->where('min_year', '>=', $params['end_time']);
            }
            $eventIds = $eventQuery->column('event_id');
            if (!empty($eventIds)) {
                $query = $query->whereIn('event_id', $eventIds);
            }
        }

        return $query->group($field)->column($field);
    }

    public function getAllSubject($params)
    {
        $query = $this->eventSubjectModel->order('id desc');

        //根据时间筛选
        if ( (isset($params['start_time']) && !empty($params['start_time'])) || (isset($params['end_time']) && !empty($params['end_time'])) ) {
            $eventQuery = $this->eventModel;
            if (isset($params['start_time']) && !empty($params['start_time'])) {
                $eventQuery = $eventQuery->where('max_year', '<=', $params['start_time']);
            }
            if (isset($params['end_time']) && !empty($params['end_time'])) {
                $eventQuery = $eventQuery->where('min_year', '>=', $params['end_time']);
            }
            $eventIds = $eventQuery->column('event_id');
            if (!empty($eventIds)) {
                $query = $query->whereIn('event_id', $eventIds);
            }
        }

        //学科
        if (isset($params['field']) && !empty($params['field'])) {
            $fieldEventIds = $this->eventFieldModel
                ->whereLike('full_name', "%{$params['field']}%")
                ->column('event_id');
            if (!empty($fieldEventIds)) {
                $query = $query->whereIn('event_id', $fieldEventIds);
            }
        }

        $subjectIds = $query->column('subject_id');
        return $this->subjectModel->whereIn('subject_id', $subjectIds)->column('name');
    }

    public function getEventList($params)
    {
        $events = [];
        $query = $this->eventModel->order('timestamp desc');

        //时间筛选
        if (isset($params['start_time']) && !empty($params['start_time'])) {
            $query = $query->where('max_year', '>=', $params['start_time']);
        }
        if (isset($params['end_time']) && !empty($params['end_time'])) {
            $query = $query->where('min_year', '<=', $params['end_time']);
        }

        //学科筛选
        if (isset($params['field']) && !empty($params['field'])) {
            $fieldEventIds = $this->eventFieldModel
                ->whereLike('full_name', "%{$params['field']}%")
                ->column('event_id');
            if (!empty($fieldEventIds)) {
                $query = $query->whereIn('event_id', $fieldEventIds);
            }
        }

        //人物筛选
        if (isset($params['people']) && !empty($params['people'])) {
            $subjectIds = $this->subjectModel
                ->whereLike('name', "%{$params['people']}%")
                ->column('subject_id');
            $subjectEventIds = $this->eventSubjectModel
                ->whereIn('subject_id', $subjectIds)
                ->column('event_id');
            if (!empty($subjectEventIds)) {
                $query = $query->whereIn('event_id', $subjectEventIds);
            }
        }

        //关键词筛选
        if (isset($params['keyword']) && !empty($params['keyword'])) {
            //事件名称
            $query = $query->whereLike('name', "%{$params['keyword']}%");

            //演进关系
            $themeEventIds = $this->eventEvolveThemeModel
                ->whereLike('theme', "%{$params['keyword']}%")
                ->column('event_id');
            if (!empty($subjectEventIds)) {
                $query = $query->whereOr('event_id', 'in', $themeEventIds);
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
                //获取关联的学科信息
                $fieldInfo = $this->eventFieldModel->where('event_id', $item['event_id'])->order('id', 'desc')->find();
                //年份数据
                $year = $item['min_year'];
                if (isset($params['start_time']) && !empty($params['start_time']) && $params['start_time'] > $item['min_year']) {
                    $year = $params['start_time'];
                }

                //整理数据
                $eventItem = [
                    'event_id' => $item['event_id'],
                    'year' => $year,
                    'time' => $item['time'],
                    'name' => $item['name'],
                    'field' => [
                        'full_name' => empty($fieldInfo) ? "" : $fieldInfo['full_name'],
                        'level_0' => empty($fieldInfo) ? "" : $fieldInfo['level_0_name'],
                        'level_1' => empty($fieldInfo) ? "" : $fieldInfo['level_1_name'],
                        'level_2' => empty($fieldInfo) ? "" : $fieldInfo['level_2_name'],
                    ],
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