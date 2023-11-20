<?php

namespace app\repository;

use app\model\Event;
use app\repository\BaseRepository;
use app\service\TokenService;
use think\facade\Cache;
use think\facade\Request;

trait CommonTrait
{
    public function _handlerEventTimeToYear($time)
    {
        $timeArr = explode('年', $time);
        $year = $timeArr[0];

        if (strpos($year, '~') !== false) {
            $timeArr = explode('~', $year);
            $year = $timeArr[0];
        }
        if (strpos($year, '～') !== false) {
            $timeArr = explode('～', $year);
            $year = $timeArr[0];
        }
        if (strpos($year, '-') !== false) {
            $timeArr = explode('-',$year);
            $year = $timeArr[0];
        }
        if (strpos($year, '公元') !== false && strpos($year, '公元前') === false) {
            $timeArr = explode('公元', $year);
            $year = $timeArr[1];
        }
        if (strpos($year, '公元前') !== false) {
            $timeArr = explode('公元前', $timeArr[0]);
            $year = '-' . $timeArr[1];
        }
        if (strpos($year, '世纪') !== false) {
            if (strpos($year, '世纪中叶') !== false) {
                $timeArr = explode('世纪', $year);
                $year = empty($timeArr[0]) ? 40 : ($timeArr[0] - 1) * 100 + 40;
            } else {
                $timeArr = explode('世纪', $year);
                $first = empty($timeArr[0]) ? 0 : ($timeArr[0] - 1) * 100;
                $second = empty($timeArr[1]) ? 0 : $timeArr[1];
                $year = $first + $second;
            }
        }
        return intval($year);
    }

    public function _getTimestamp($year)
    {
        return $year * 372 - 31 - 1;
    }

    public function _queryAllEventV1($params)
    {
        $query = $this->eventModel
            ->whereNotNull('timestamp')
            ->where('timestamp', '>=', $this->_getTimestamp(Event::START_YEAR))
            ->where('timestamp', '<=', $this->_getTimestamp(Event::END_YEAR));

        //时间筛选
        if (isset($params['time']) && !empty($params['time'])) {
            $timestamp = $this->_getTimestamp($params['time']);
            $query = $query->where('timestamp', $timestamp);
        }

        //学科筛选
        if (isset($params['field']) && !empty($params['field'])) {
            $fieldEventIds = $this->eventFieldModel
                ->where('level_1_name', $params['field'])
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
            $query = $query->whereLike('name|object', "%{$params['keyword']}%");
        }

        //演进关系筛选
        if (isset($params['theme']) && !empty($params['theme'])) {
            $themeEventIds = $this->eventEvolveThemeModel
                ->whereLike('theme', "%{$params['theme']}%")
                ->column('event_id');
            if (!empty($themeEventIds)) {
                $query = $query->whereIn('event_id', $themeEventIds);
            }
        }

        // 标签筛选
        if (isset($params['tag']) && !empty($params['tag'])) {
            $tagEventIds = $this->eventTagModel->alias('et')
                ->leftjoin('tag t', 't.tag_id = et.tag_id')
                ->whereLike('t.name', "%{$params['tag']}%")
                ->column('et.event_id');
            if (!empty($tagEventIds)) {
                $query = $query->whereIn('event_id', $tagEventIds);
            }
        }

        //查询到的所有事件
        return $query->order('timestamp', 'asc')->column('event_id');
    }

    public function _queryAllEventV2($maxNumber, $params)
    {
        $query = $this->eventModel
            ->whereNotNull('timestamp')
            ->where('timestamp', '>=', $this->_getTimestamp(Event::START_YEAR))
            ->where('timestamp', '<=', $this->_getTimestamp(Event::END_YEAR));

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
            $query = $query->whereLike('name|object', "%{$params['keyword']}%");
        }

        //演进关系筛选
        if (isset($params['theme']) && !empty($params['theme'])) {
            $themeEventIds = $this->eventEvolveThemeModel
                ->whereLike('theme', "%{$params['theme']}%")
                ->column('event_id');
            if (!empty($subjectEventIds)) {
                $query = $query->whereIn('event_id', $themeEventIds);
            }
        }

        // 标签筛选
        if (isset($params['tag']) && !empty($params['tag'])) {
            $tagEventIds = $this->eventTagModel->alias('et')
                ->leftjoin('tag t', 't.tag_id = et.tag_id')
                ->whereLike('t.name', "%{$params['tag']}%")
                ->column('et.event_id');
            if (!empty($tagEventIds)) {
                $query = $query->whereIn('event_id', $tagEventIds);
            }
        }

        //查询到的所有事件
        $upEventIds = $query->order('timestamp', 'asc')
            ->limit(0, floor($maxNumber / 2))
            ->column('event_id');

        $downEventIds = $query->order('timestamp', 'desc')
            ->limit(0, floor($maxNumber / 2))
            ->column('event_id');

        return array_merge($upEventIds, $downEventIds);
    }

    public function _queryAllEventV3($params)
    {
        $query = $this->eventModel
            ->whereNotNull('timestamp')
            ->where('timestamp', '>=', $this->_getTimestamp(Event::START_YEAR))
            ->where('timestamp', '<=', $this->_getTimestamp(Event::END_YEAR));

        if (isset($params['start_time']) && !empty($params['start_time'])) {
            $query = $query->where('timestamp', '>=', $this->_getTimestamp($params['start_time']));
        }

        if (isset($params['end_time']) && !empty($params['end_time'])) {
            $query = $query->where('timestamp', '<=', $this->_getTimestamp($params['end_time']));
        }

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
            $query = $query->whereLike('name|object', "%{$params['keyword']}%");
        }

        //演进关系筛选
        if (isset($params['theme']) && !empty($params['theme'])) {
            $themeEventIds = $this->eventEvolveThemeModel
                ->whereLike('theme', "%{$params['theme']}%")
                ->column('event_id');
            if (!empty($themeEventIds)) {
                $query = $query->whereIn('event_id', $themeEventIds);
            }
        }

        // 标签筛选
        if (isset($params['tag']) && !empty($params['tag'])) {
            $tagEventIds = $this->eventTagModel->alias('et')
                ->leftjoin('tag t', 't.tag_id = et.tag_id')
                ->whereLike('t.name', "%{$params['tag']}%")
                ->column('et.event_id');
            if (!empty($tagEventIds)) {
                $query = $query->whereIn('event_id', $tagEventIds);
            }
        }

        //查询到的所有事件
        return $query->order('timestamp', 'asc')->column('event_id');
    }

    public function _handlerEvent($minTime, $maxTime, $eventId, $timeRange = [])
    {
        //查询事件
        $eventInfo = $this->eventModel->where('event_id', $eventId)->find();
        $item = $this->_handlerEventItem($eventInfo);

        //时间范围筛选
        if (!empty($timeRange) && !empty($timeRange['start']) && !empty($timeRange['end'])) {
            if ($item['year'] < $timeRange['start'] || $item['year'] > $timeRange['end']) {
                return [ [], $minTime, $maxTime ];
            }
        }

        //年份数据
        if (empty($minTime['sort'])) {
            $minTime = [
                'year' => $eventInfo['formated_time'],
                'sort' => $eventInfo['timestamp'],
            ];
        }
        if (empty($maxTime['sort'])) {
            $maxTime = [
                'year' => $eventInfo['formated_time'],
                'sort' => $eventInfo['timestamp'],
            ];
        }
        if ($minTime['sort'] > $eventInfo['timestamp'] && !empty($eventInfo['formated_time'])) {
            $minTime = [
                'year' => $eventInfo['formated_time'],
                'sort' => $eventInfo['timestamp'],
            ];
        }
        if ($maxTime['sort'] < $eventInfo['timestamp'] && !empty($eventInfo['formated_time'])) {
            $maxTime = [
                'year' => $eventInfo['formated_time'],
                'sort' => $eventInfo['timestamp'],
            ];
        }

        return [ $item, $minTime, $maxTime ];
    }

    public function _handlerEventItem($eventInfo)
    {
        //下一级事件
        $childEventId = $this->eventRelationModel
            ->where('target_event_id', $eventInfo['event_id'])
            ->column('source_event_id');

        //上一级事件
        $parentEventId = $this->eventRelationModel
            ->where('source_event_id', $eventInfo['event_id'])
            ->column('target_event_id');

        //查找是否存在于演进主题中
        $evolveTheme = $this->eventEvolveThemeModel
            ->where('event_id', $eventInfo['event_id'])
            ->column('theme');

        //获取关联的学科信息
        $fieldInfo = $this->eventFieldModel
            ->where('event_id', $eventInfo['event_id'])
            ->order('id', 'desc')
            ->field('level_0_name, level_1_name')
            ->find();

        //获取关联的标签信息
        $tags = $this->eventTagModel->alias('et')
            ->leftjoin('tag t', 't.tag_id = et.tag_id')
            ->where('et.event_id', $eventInfo['event_id'])
            ->column('t.name');

        if (empty($fieldInfo['level_1_name'])) {
            return false;
        }

        //整理数据
        return [
            'event_id' => $eventInfo['event_id'],
            'year' => $this->_handlerEventTimeToYear($eventInfo['formated_time']),
            'time' => $eventInfo['time'],
            'name' => $eventInfo['name'],
            'object' => $eventInfo['object'],
            'field' => $fieldInfo['level_1_name'],
            'field_level_1' => $fieldInfo['level_1_name'],
            'field_level_0' => $fieldInfo['level_0_name'],
            'tags' => empty($tags) ? [] : $tags,
            'relation' => [
                'parent' => empty($parentEventId) ? [] : $parentEventId,
                'child' => empty($childEventId) ? [] : $childEventId,
            ],
            'evolve_info' => [
                'has' => !empty($evolveTheme),
                'theme' => empty($evolveTheme) ? [] : $evolveTheme,
            ],
        ];
    }

    public function _getChildrenEvent($maxNumber, $timeRange, $list, $minTime, $maxTime, $eventId)
    {
        //够数量，直接返回
//        if (count($list) >= $maxNumber) {
//            return [ $list, $minTime, $maxTime ];
//        }

        //查找事件
        $childrenEventIds = $this->eventRelationModel
            ->where('target_event_id', $eventId)
            ->column('source_event_id');

        //没有事件，直接返回
        if (empty($childrenEventIds)) {
            return [ $list, $minTime, $maxTime ];
        }

        //处理事件
        foreach ($childrenEventIds as $childrenEventId) {
            list($eventItem, $minTime, $maxTime) = $this->_handlerEvent($minTime, $maxTime, $childrenEventId, $timeRange);
            if (!empty($eventItem)) {
                $list[] = $eventItem;
            }
            //递归查询
            return $this->_getChildrenEvent($maxNumber, $timeRange, $list, $minTime, $maxTime, $childrenEventId);
        }
    }

    public function _getChildrenEventV1($startTime, $endTime, $parentEventIds)
    {
        //查找事件
        /*$childrenEventId = $this->eventRelationModel
            ->whereIn('target_event_id', $parentEventIds)
            ->column('source_event_id');*/

        $childrenEventId = $this->eventRelationModel->alias('er')
            ->leftjoin('event e', 'e.event_id = er.target_event_id')
            ->where('e.timestamp', '>=', $this->_getTimestamp($startTime))
            ->where('e.timestamp', '<=', $this->_getTimestamp($endTime))
            ->whereIn('er.target_event_id', $parentEventIds)
            ->column('er.source_event_id');

        //没有事件，直接返回
        if (empty($childrenEventId)) {
            return [];
        }
        return $childrenEventId;
    }

    public function _getChildrenEventV2($timeRange, $eventId, $childrenEventIds)
    {
        //查找事件
        $nowEventIds = $this->eventRelationModel->alias('er')
            ->leftJoin('event e', 'e.event_id = er.source_event_id')
            ->whereNotNull('e.timestamp')
            ->where('e.timestamp', '>=', $this->_getTimestamp($timeRange['start']))
            ->where('e.timestamp', '<=', $this->_getTimestamp($timeRange['end']))
            ->whereIn('er.target_event_id', $eventId)
            ->column('er.source_event_id');

        //没有事件，直接返回
        if (empty($nowEventIds)) {
            return $childrenEventIds;
        }
        $childrenEventIds = array_merge($childrenEventIds, $nowEventIds);
        return $this->_getChildrenEventV2($timeRange, $nowEventIds, $childrenEventIds);
    }

    public function _getParentEvent($maxNumber, $timeRange, $list, $minTime, $maxTime, $eventId)
    {
        //够数量，直接返回
//        if (count($list) >= $maxNumber) {
//            return [ $list, $minTime, $maxTime ];
//        }

        //查找事件
        $parentEventIds = $this->eventRelationModel
            ->where('source_event_id', $eventId)
            ->column('target_event_id');

        //没有事件，直接返回
        if (empty($parentEventIds)) {
            return [ $list, $minTime, $maxTime ];
        }

        //处理事件
        foreach ($parentEventIds as $parentEventId) {
            list($eventItem, $minTime, $maxTime) = $this->_handlerEvent($minTime, $maxTime, $parentEventId, $timeRange);
            if (!empty($eventItem)) {
                $list[] = $eventItem;
            }

            //递归查询
            return $this->_getParentEvent($maxNumber, $timeRange, $list, $minTime, $maxTime, $parentEventId);
        }
    }

    public function _getParentEventV1($startTime, $endTime, $childrenEventIds)
    {
        //查找事件
//        $parentEventId = $this->eventRelationModel
//            ->whereIn('source_event_id', $childrenEventIds)
//            ->column('target_event_id');

        $parentEventId = $this->eventRelationModel->alias('er')
            ->leftjoin('event e', 'e.event_id = er.source_event_id')
            ->where('e.timestamp', '>=', $this->_getTimestamp($startTime))
            ->where('e.timestamp', '<=', $this->_getTimestamp($endTime))
            ->whereIn('er.source_event_id', $childrenEventIds)
            ->column('er.target_event_id');

        //没有事件，直接返回
        if (empty($parentEventId)) {
            return [];
        }
        return $parentEventId;
    }

    public function _getParentEventV2($timeRange, $eventId, $parentEventIds)
    {
        //查找事件
        $nowEventIds = $this->eventRelationModel->alias('er')
            ->leftJoin('event e', 'e.event_id = er.target_event_id')
            ->whereNotNull('e.timestamp')
            ->where('e.timestamp', '>=', $this->_getTimestamp($timeRange['start']))
            ->where('e.timestamp', '<=', $this->_getTimestamp($timeRange['end']))
            ->whereIn('er.source_event_id', $eventId)
            ->column('er.target_event_id');

        //没有事件，直接返回
        if (empty($nowEventIds)) {
            return $parentEventIds;
        }
        $parentEventIds = array_merge($parentEventIds, $nowEventIds);
        return $this->_getParentEventV2($timeRange, $nowEventIds, $parentEventIds);
    }

    public function _getNextEvent($eventId, $checkEventIds)
    {
        $nextEventId = $this->eventRelationModel
            ->where('target_event_id', $eventId)
            ->value('source_event_id');
        if (empty($nextEventId)) {
            return false;
        }
        if (!in_array($nextEventId, $checkEventIds)) {
            return $this->_getNextEvent($nextEventId, $checkEventIds);
        }
        return $nextEventId;
    }
}