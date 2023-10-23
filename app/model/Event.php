<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Event extends Model
{
    protected $table = 'event';

    const START_YEAR = -5000;
    const END_YEAR = 2060;
}
