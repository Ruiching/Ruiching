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
            ->where('timestamp', '>', 0)
            ->where('formated_time', '<', '2060年')
            ->order('timestamp', 'asc')
            ->value('formated_time');

        $endTime = $this->eventModel
            ->where('timestamp', '>', 0)
            ->where('formated_time', '<', '2060年')
            ->order('timestamp', 'desc')
            ->value('formated_time');

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
                        $childrenList[] = [
                            'field' => $value,
                        ];
                    }
                }

                $fieldItem = [
                    'field' => $oneLevel,
                    'children' => $childrenList
                ];
                $fields[] = $fieldItem;
            }
        }

        return $fields;
    }

    public function getAllSubject($params)
    {
        $page = !empty($params['page']) ? intval($params['page']) : 1;
        $pageNumber = !empty($params['page_number']) ? intval($params['page_number']) : 20;

        $query = $this->eventSubjectModel->order('id desc');

        //学科筛选
        if (isset($params['field']) && !empty($params['field'])) {
            $fieldEventIds = $this->eventFieldModel
                ->whereLike('level_1_name', $params['field'])
                ->column('event_id');
            if (!empty($fieldEventIds)) {
                $query = $query->whereIn('event_id', $fieldEventIds);
            }
        }

        $subjectIds = $query->column('subject_id');
        return $this->subjectModel->whereIn('subject_id', $subjectIds)
            ->order('name', 'asc')
            ->limit(($page - 1) * $pageNumber, $pageNumber)
            ->column('name');
    }

    public function getEventList($params)
    {
        $eventMaxNumber = !empty($params['max_number']) ? intval($params['max_number']) : 200;
        if ($eventMaxNumber > 500) {
            $eventMaxNumber = 500;
        }

        $events = [];
        $minTime = $maxTime = [
            'year' => '',
            'sort' => 0,
        ];

//        $eventIds = $this->_queryAllEventV2($eventMaxNumber, $params);
//        if (!empty($eventIds)) {
//            foreach ($eventIds as $eventId) {
//                //加入本体事件
//                list($events, $minTime, $maxTime) = $this->_handlerEvent($events, $minTime, $maxTime, $eventId);
//
//                //查询下级事件信息
//                list($events, $minTime, $maxTime) = $this->_getChildrenEvent($eventMaxNumber, $events, $minTime, $maxTime, $eventId);
//
//                //查询上级事件信息
//                list($events, $minTime, $maxTime) = $this->_getParentEvent($eventMaxNumber, $events, $minTime, $maxTime, $eventId);
//
//                if (count($events) >= $eventMaxNumber) {
//                    break;
//                }
//            }
//        }

        $i = 1;
        while (true) {

            $eventIds = $this->_queryAllEventV1($params);
            if (!empty($eventIds)) {
                foreach ($eventIds as $eventId) {
                    //加入本体事件
                    list($events, $minTime, $maxTime) = $this->_handlerEvent($events, $minTime, $maxTime, $eventId);

                    //查询下级事件信息
                    list($events, $minTime, $maxTime) = $this->_getChildrenEvent($eventMaxNumber, $events, $minTime, $maxTime, $eventId);

                    //查询上级事件信息
                    list($events, $minTime, $maxTime) = $this->_getParentEvent($eventMaxNumber, $events, $minTime, $maxTime, $eventId);

                    if (count($events) >= $eventMaxNumber) {
                        break;
                    }
                }
            }

            if (count($events) >= $eventMaxNumber) {
                break;
            }

            $params['time'] = $i % 2 == 0 ? ($params['time'] - $i) : ($params['time'] + $i);
            $i++;
        }

        //整理列表
        $lists = [
            'events' => [],
            'startYear' => $minTime['year'],
            'endYear' => $maxTime['year'],
            'count' => count($events),
        ];
        if (!empty($events)) {
            foreach ($events as $item) {
                if (empty($lists['events'][$item['field']]['field'])) {
                    $lists['events'][$item['field']]['field'] = $item['field'];
                }
                $lists['events'][$item['field']]['events'][] = $item;
            }
        }

        return $lists;
    }

    public function getEvolveList($params)
    {
        $events = [];
        $eventIds = $this->eventEvolveThemeModel->where('theme', $params['theme'])->column('event_id');
        $list = $this->eventModel->whereIn('event_id', $eventIds)->order('timestamp', 'desc')->select();
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

    public function getRecommendList($params)
    {
        $limitNumber = !empty($params['limit_num']) ? intval($params['limit_num']) : 10;
        if ($limitNumber > 100) {
            $limitNumber = 100;
        }

        $query = $this->eventModel
            ->where('timestamp', '>', 0)
            ->where('formated_time', '<', '2060年');

        //时间筛选
        if (empty($params['time'])) {
            $params['time'] = date('Y', time());
        }
        $query = $query->whereLike('formated_time', "%{$params['time']}年%");

        //学科筛选
        if (isset($params['field']) && !empty($params['field'])) {
            $fieldEventIds = $this->eventFieldModel
                ->whereLike('level_1_name', $params['field'])
                ->column('event_id');
            if (!empty($fieldEventIds)) {
                $query = $query->whereIn('event_id', $fieldEventIds);
            }
        }

        //查询到的所有事件
        $events = [];
        $lists = $query->order('timestamp', 'desc')->limit($limitNumber)->select();
        if (!empty($lists)) {
            foreach ($lists as $item) {
                $events[] = $this->_handlerEventItem($item);
            }
        }
        return $events;
    }
}