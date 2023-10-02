<?php

namespace app\repository;

use app\repository\BaseRepository;
use think\facade\Cache;

trait CommonTrait
{
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