<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class EventReference extends Model
{
    protected $table = 'event__reference';
}
