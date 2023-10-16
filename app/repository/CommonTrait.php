<?php

namespace app\repository;

use app\repository\BaseRepository;
use think\facade\Cache;

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
        return $year;
    }

    public function _queryAllEventV1($params)
    {
        $query = $this->eventModel
            ->where('timestamp', '>', 0)
            ->where('formated_time', '<', '2060年');

        //时间筛选
        if (isset($params['time']) && !empty($params['time'])) {
            $query->whereLike('formated_time', "%{$params['time']}年%");
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
            //事件名称
            $query = $query->whereLike('name|object', "%{$params['keyword']}%");

            //演进关系
            $themeEventIds = $this->eventEvolveThemeModel
                ->whereLike('theme', "%{$params['keyword']}%")
                ->column('event_id');
            if (!empty($subjectEventIds)) {
                $query = $query->whereOr('event_id', 'in', $themeEventIds);
            }
        }

        //查询到的所有事件
        return $query->order('timestamp', 'asc')->column('event_id');
    }

    public function _queryAllEventV2($maxNumber, $params)
    {
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
            $query = $query->whereLike('name|object', "%{$params['keyword']}%");

            //演进关系
            $themeEventIds = $this->eventEvolveThemeModel
                ->whereLike('theme', "%{$params['keyword']}%")
                ->column('event_id');
            if (!empty($subjectEventIds)) {
                $query = $query->whereOr('event_id', 'in', $themeEventIds);
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

    public function _handlerEvent($list, $minTime, $maxTime, $eventId)
    {
        //查询事件
        $eventInfo = $this->eventModel->where('event_id', $eventId)->find();

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
        if ($minTime['sort'] > $eventInfo['timestamp']) {
            $minTime = [
                'year' => $eventInfo['formated_time'],
                'sort' => $eventInfo['timestamp'],
            ];
        }
        if ($maxTime['sort'] < $eventInfo['timestamp']) {
            $maxTime = [
                'year' => $eventInfo['formated_time'],
                'sort' => $eventInfo['timestamp'],
            ];
        }

        $list[] = $this->_handlerEventItem($eventInfo);
        return [ $list, $minTime, $maxTime ];
    }

    public function _handlerEventItem($eventInfo)
    {
        //下一级事件
        $childEventId = $this->eventRelationModel
            ->where('target_event_id', $eventInfo['event_id'])
            ->value('source_event_id');

        //上一级事件
        $parentEventId = $this->eventRelationModel
            ->where('source_event_id', $eventInfo['event_id'])
            ->value('target_event_id');

        //查找是否存在于演进主题中
        $evolveTheme = $this->eventEvolveThemeModel
            ->where('event_id', $eventInfo['event_id'])
            ->value('theme');

        //获取关联的学科信息
        $field = $this->eventFieldModel
            ->where('event_id', $eventInfo['event_id'])
            ->order('id', 'desc')
            ->value('level_1_name');

        //整理数据
        return [
            'event_id' => $eventInfo['event_id'],
            'year' => $this->_handlerEventTimeToYear($eventInfo['formated_time']),
            'time' => $eventInfo['time'],
            'name' => $eventInfo['name'],
            'object' => $eventInfo['object'],
            'field' => $field,
            'relation' => [
                'parent' => empty($parentEventId) ? '' : $parentEventId,
                'child' => empty($childEventId) ? '' : $childEventId,
            ],
            'evolve_info' => [
                'has' => !empty($evolveTheme),
                'theme' => empty($evolveTheme) ? "" : $evolveTheme,
            ],
        ];
    }

    public function _getChildrenEvent($maxNumber, $list, $minTime, $maxTime, $eventId)
    {
        //够数量，直接返回
        if (count($list) >= $maxNumber) {
            return [ $list, $minTime, $maxTime ];
        }

        //查找事件
        $childrenEventId = $this->eventRelationModel
            ->where('target_event_id', $eventId)
            ->value('source_event_id');

        //没有事件，直接返回
        if (empty($childrenEventId)) {
            return [ $list, $minTime, $maxTime ];
        }

        //处理事件
        list($list, $minTime, $maxTime) = $this->_handlerEvent($list, $minTime, $maxTime, $childrenEventId);

        //递归查询
        return $this->_getChildrenEvent($maxNumber, $list, $minTime, $maxTime, $childrenEventId);
    }

    public function _getParentEvent($maxNumber, $list, $minTime, $maxTime, $eventId)
    {
        //够数量，直接返回
        if (count($list) >= $maxNumber) {
            return [ $list, $minTime, $maxTime ];
        }

        //查找事件
        $parentEventId = $this->eventRelationModel
            ->where('source_event_id', $eventId)
            ->value('target_event_id');

        //没有事件，直接返回
        if (empty($parentEventId)) {
            return [ $list, $minTime, $maxTime ];
        }

        //处理事件
        list($list, $minTime, $maxTime) = $this->_handlerEvent($list, $minTime, $maxTime, $parentEventId);

        //递归查询
        return $this->_getParentEvent($maxNumber, $list, $minTime, $maxTime, $parentEventId);
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