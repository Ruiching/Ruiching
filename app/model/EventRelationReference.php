<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class EventRelationReference extends Model
{
    protected $table = 'event_relation__reference';
}
