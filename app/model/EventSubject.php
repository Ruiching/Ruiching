<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class EventSubject extends Model
{
    protected $table = 'event__subject';
}
