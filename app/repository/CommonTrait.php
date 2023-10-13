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

        if (strpos($year, '～') !== false) {
            $timeArr = explode('～', $year);
            $year = $timeArr[0];
        }
        if (strpos($year, '-') !== false) {
            $timeArr = explode('-',$year);
            $year = $timeArr[0];
        }
        if (strpos($year, '世纪') !== false) {
            if (strpos($year, '世纪中叶') !== false) {
                $timeArr = explode('世纪', $year);
                $year = ($timeArr[0] - 1) * 100 + 40;
            } else {
                $timeArr = explode('世纪', $year);
                $year = ($timeArr[0] - 1) * 100 + $timeArr[1];
            }
        }
        if (strpos($year, '公元') !== false && strpos($year, '公元前') === false) {
            $timeArr = explode('公元', $year);
            $year = $timeArr[1];
        }
        if (strpos($year, '公元前') !== false) {
            $timeArr = explode('公元前', $timeArr[0]);
            $year = '-' . $timeArr[1];
        }
        return $year;
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