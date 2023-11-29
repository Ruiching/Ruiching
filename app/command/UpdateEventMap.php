<?php
declare (strict_types = 1);

namespace app\command;

use app\libs\RedisLock;
use app\model\Event;
use app\model\EventField;
use app\repository\BaseRepository;
use app\repository\CommonTrait;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Cache;

class UpdateEventMap extends Command
{
    use CommonTrait;

    protected function configure()
    {
        // 指令配置
        $this->setName('update_event_map')->setDescription('更新事件分布地图');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('更新事件分布地图开始');

        $map = [];
        $repository = new BaseRepository();
        $fields = $repository->eventFieldModel->group('level_1_name')->column('level_1_name');
        foreach ($fields as $field) {
            $map[$field] = [];
            // 根据时间进行分组
            $mixTime = Event::START_YEAR;
            $maxTime = Event::END_YEAR + 40;
            for ($i = $mixTime; $i <= $maxTime; $i += 100) {
                $startTime = $this->_getTimestamp($i);
                $endTime = $this->_getTimestamp($i + 100);
                $eventCount = $repository->eventModel->alias('e')
                    ->leftJoin(EventField::TABLE_NAME ." ef", 'ef.event_id = e.id')
                    ->where('ef.level_1_name', $field)
                    ->whereNotNull('e.timestamp')
                    ->where('e.timestamp', '>=', $startTime)
                    ->where('e.timestamp', '<', $endTime)
                    ->count();
                $map[$field][$i] = empty($eventCount) ? 0 : intval($eventCount);
                $output->writeln("学科：{$field}，时间范围：{$startTime} ~ {$endTime} 内有 {$eventCount} 个事件");
            }
        }
        Cache::set('event_map', $map, 86400);

        $output->writeln('更新事件分布地图结束');
    }
}
