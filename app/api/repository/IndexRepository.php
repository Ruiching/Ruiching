<?php


namespace app\api\repository;

use app\model\Event;
use app\repository\BaseRepository;
use app\repository\CommonTrait;
use app\Request;
use think\facade\Cache;
use think\facade\Env;

class IndexRepository extends BaseRepository
{
    use CommonTrait;

    public function getAllTime()
    {
//        $startTime = $this->eventModel
//            ->where('timestamp', '>', 0)
//            ->where('formated_time', '<', '2060年')
//            ->order('timestamp', 'asc')
//            ->value('formated_time');
//
//        $endTime = $this->eventModel
//            ->where('timestamp', '>', 0)
//            ->where('formated_time', '<', '2060年')
//            ->order('timestamp', 'desc')
//            ->value('formated_time');
//
//        $startYear = $this->_handlerEventTimeToYear($startTime);
//        $endYear = $this->_handlerEventTimeToYear($endTime);

        $startTime = $this->eventModel
            ->whereNotNull('timestamp')
            ->where('timestamp', '>=', $this->_getTimestamp(Event::START_YEAR))
            ->where('timestamp', '<=', $this->_getTimestamp(Event::END_YEAR))
            ->order('timestamp', 'asc')
            ->min('timestamp');

        $endTime = $this->eventModel
            ->whereNotNull('timestamp')
            ->where('timestamp', '>=', $this->_getTimestamp(Event::START_YEAR))
            ->where('timestamp', '<=', $this->_getTimestamp(Event::END_YEAR))
            ->order('timestamp', 'desc')
            ->max('timestamp');

        $startYear = floor(($startTime + 31 + 1) / 372);
        $endYear = floor(($endTime + 31 + 1) / 372);

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

        $eventTimeRange = !empty($params['time_range']) ? intval($params['time_range']) : 0;

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

        $timeRange = [
            'start' => '',
            'end' => '',
        ];
        if (isset($params['time']) && !empty($params['time']) && $eventTimeRange > 0) {
            $timeRange = [
                'start' => $params['time'] - $eventTimeRange,
                'end' => $params['time'] + $eventTimeRange,
            ];
        }

        $i = 1;
        while (true) {

            $eventIds = $this->_queryAllEventV1($params);
            if (!empty($eventIds)) {
                foreach ($eventIds as $eventId) {
                    //加入本体事件
                    list($eventItem, $minTime, $maxTime) = $this->_handlerEvent($minTime, $maxTime, $eventId, []);
                    if (!empty($eventItem)) {
                        $events[] = $eventItem;

                        //查询下级事件信息
                        list($events, $minTime, $maxTime) = $this->_getChildrenEvent($eventMaxNumber, $timeRange, $events, $minTime, $maxTime, $eventId);

                        //查询上级事件信息
                        list($events, $minTime, $maxTime) = $this->_getParentEvent($eventMaxNumber, $timeRange, $events, $minTime, $maxTime, $eventId);

                        if (count($events) >= $eventMaxNumber) {
                            break;
                        }
                    }
                }
            }

            //时间范围筛选
            if (!empty($timeRange) && !empty($timeRange['start']) && !empty($timeRange['end'])) {
                if ($params['time'] < $timeRange['start'] || $params['time'] > $timeRange['end']) {
                    break;
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
            'start_time' => $minTime['year'],
            'end_time' => $maxTime['year'],
            'startYear' => $this->_handlerEventTimeToYear($minTime['year']),
            'endYear' => $this->_handlerEventTimeToYear($maxTime['year']),
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

    public function getEventListV2($params)
    {
        $events = [];
        $minTime = $maxTime = [
            'year' => '',
            'sort' => 0,
        ];

        $timeRange = !empty($params['time_range']) ? intval($params['time_range']) : 0;
        $maxLevel = !empty($params['max_level']) ? intval($params['max_level']) : 10;
        if ($maxLevel > 10) {
            $maxLevel = 10;
        }

        $eventIds = [];
        $topIds = [];
        for ($j = 0; $j < $timeRange; $j++) {
            $topEvents = $this->_queryAllEventV3($params);
            $topIds = array_merge($topIds, $topEvents);
            $params['time'] = $j % 2 == 0 ? ($params['time'] - $j) : ($params['time'] + $j);
        }
        if (!empty($topIds)) {
            $queryChildrenIds = $queryParentIds = $topIds;
            //查询下级事件信息
            for ($i = 1; $i <= $maxLevel; $i++) {
                $childrenIds = $this->_getChildrenEventV1($queryChildrenIds);
                if (empty($childrenIds)) {
                    break;
                }
                $eventIds = array_merge($eventIds, $childrenIds);
                $queryChildrenIds = $childrenIds;
            }
            //查询上级事件信息
            for ($i = 1; $i <= $maxLevel; $i++) {
                $parentIds = $this->_getParentEventV1($queryParentIds);
                if (empty($parentIds)) {
                    break;
                }
                $eventIds = array_merge($eventIds, $parentIds);
                $queryParentIds = $parentIds;
            }
            $eventIds = array_merge($eventIds, $topIds);
            //去重
            $eventIds = array_unique($eventIds);
        }

        if (!empty($eventIds)) {
            $lists = $this->eventModel->where('event_id', 'in', $eventIds)->select();
            foreach ($lists as $event) {
                $item = $this->_handlerEventItem($event);

                //年份数据
                if (empty($minTime['sort'])) {
                    $minTime = [
                        'year' => $event['formated_time'],
                        'sort' => $event['timestamp'],
                    ];
                }
                if (empty($maxTime['sort'])) {
                    $maxTime = [
                        'year' => $event['formated_time'],
                        'sort' => $event['timestamp'],
                    ];
                }
                if ($minTime['sort'] > $event['timestamp'] && !empty($event['formated_time'])) {
                    $minTime = [
                        'year' => $event['formated_time'],
                        'sort' => $event['timestamp'],
                    ];
                }
                if ($maxTime['sort'] < $event['timestamp'] && !empty($event['formated_time'])) {
                    $maxTime = [
                        'year' => $event['formated_time'],
                        'sort' => $event['timestamp'],
                    ];
                }

                if (empty($events[$item['field']]['field'])) {
                    $events[$item['field']]['field'] = $item['field'];
                }
                $events[$item['field']]['events'][] = $item;
            }
        }

        //整理列表
        return [
            'events' => $events,
            'start_time' => $minTime['year'],
            'end_time' => $maxTime['year'],
            'startYear' => $this->_handlerEventTimeToYear($minTime['year']),
            'endYear' => $this->_handlerEventTimeToYear($maxTime['year']),
            'count' => count($eventIds),
        ];
    }

    public function getEventMap($params)
    {
        if (isset($params['op']) && $params['op'] == 'refresh') {
            $map = [];
            $fields = $this->eventFieldModel->group('level_1_name')->column('level_1_name');
            foreach ($fields as $field) {
                $map[$field] = [];
                $eventIds = $this->eventFieldModel->where('level_1_name', $field)->column('event_id');
                if (!empty($eventIds)) {
                    // 根据时间进行分组
                    $mixTime = -3000;
                    $maxTime = 2060;
                    for ($i = $mixTime; $i <= $maxTime; $i += 100) {
                        $startYear = $i . "年";
                        $endYear = ($i + 100) . "年";
                        $eventCount = $this->eventModel
                            ->whereIn('event_id', $eventIds)
                            ->where('formated_time', '>=', $startYear)
                            ->where('formated_time', '<', $endYear)
                            ->count();
                        $map[$field][$i] = empty($eventCount) ? 0 : intval($eventCount);
                    }
                }
            }
            Cache::set('event_map', $map, 86400 * 30);
        } else {
            $map = Cache::get('event_map');
        }
        return $map;
    }

    public function getEvolveList($params)
    {
        $events = [];
        $eventIds = $this->eventEvolveThemeModel
            ->whereLike('theme', "%{$params['theme']}%")
            ->column('event_id');
        $list = $this->eventModel
            ->whereIn('event_id', $eventIds)
            ->whereNotNull('timestamp')
            ->where('timestamp', '>=', $this->_getTimestamp(Event::START_YEAR))
            ->where('timestamp', '<=', $this->_getTimestamp(Event::END_YEAR))
            ->order('timestamp', 'desc')
            ->select();
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
            ->whereNotNull('timestamp')
            ->where('timestamp', '>=', $this->_getTimestamp(Event::START_YEAR))
            ->where('timestamp', '<=', $this->_getTimestamp(Event::END_YEAR))
            ->order('timestamp', 'desc');

        //时间筛选
        if (isset($params['time']) && !empty($params['time'])) {
            $timestamp = $this->_getTimestamp($params['time']);
            $query = $query->where('timestamp', $timestamp);
        }

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
        $lists = $query->limit($limitNumber)->select();
        if (!empty($lists)) {
            foreach ($lists as $item) {
                $events[] = $this->_handlerEventItem($item);
            }
        }
        return $events;
    }
}