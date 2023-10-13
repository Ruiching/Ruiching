<?php


namespace app\api\repository;

use app\repository\BaseRepository;
use app\repository\CommonTrait;
use app\Request;
use think\facade\Env;

class IndexRepository extends BaseRepository
{
    use CommonTrait;

    public function getAllTime()
    {
        $startTime = $this->eventModel
            ->order('timestamp', 'asc')
            ->value('time');

        $endTime = $this->eventModel
            ->order('timestamp', 'desc')
            ->value('time');

        $startYear = $this->_handlerEventTimeToYear($startTime);
        $endYear = $this->_handlerEventTimeToYear($endTime);

        return [
            'startYear' => $startYear,
            'endYear' => $endYear,
        ];
    }

    public function getAllFields()
    {
        $fields = [];

        //0级学科
        $oneList = $this->eventFieldModel->group('level_0_name')->column('level_0_name');
        if (!empty($oneList)) {
            foreach ($oneList as $oneLevel) {
                $childrenList = [];

                //1级学科
                $childrenField = $this->eventFieldModel
                    ->where('level_0_name', $oneLevel)
                    ->group('level_1_name')
                    ->column('level_1_name');
                if (!empty($childrenField)) {
                    foreach ($childrenField as $value) {
                        $childrenList = [
                            'field' => $value,
                        ];
                    }
                }

                $fieldItem = [
                    'field' => $oneList,
                    'children' => $childrenList
                ];
                $fields[] = $fieldItem;
            }
        }

        return $fields;
    }

    public function getAllSubject()
    {
        $subjectIds = $this->eventSubjectModel->order('id desc')->column('subject_id');
        return $this->subjectModel->whereIn('subject_id', $subjectIds)->column('name');
    }

    public function getEventList($params)
    {
        $events = [];
        $eventNumber = intval($params['event_number']);
        if ($eventNumber > 500) {
            $eventNumber = 500;
        }

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
                    'object' => $item['object'],
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
                    'object' => $item['object'],
                    'next_event_id' => empty($nextEventId) ? "" : $nextEventId,
                ];
                $events[] = $eventItem;
            }
        }
        return $events;
    }
}