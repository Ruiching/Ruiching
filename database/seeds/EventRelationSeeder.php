<?php

use think\migration\Seeder;

class EventRelationSeeder extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $eventRelation = [];
        $sourceEventIds = [];
        $eventModel = new \app\model\Event();
        $eventIds = $eventModel->column('event_id');

        $eventRelationModel = new \app\model\EventRelation();
        $eventRelationModel->where('relation_type', 'DEPEND_ON')->update(['source_event_id' => '', 'target_event_id' => '']);

        while (true) {
            // 随机取部分关系ID
            $relationIds = $eventRelationModel
                ->where('source_event_id', '')
                ->limit(0, rand(1, 20))
                ->column('relation_id');
            if (empty($relationIds)) {
                break;
            }
            foreach ($relationIds as $relationId) {
                // 随机获取事件ID
                $index = rand(0, count($eventIds) - 2);
                $sourceEventId = $eventIds[$index];
                $targetEventId = $eventIds[$index + 1];
                $eventRelationModel->where('relation_id',  $relationId)->update([
                    'source_event_id' => $sourceEventId,
                    'target_event_id' => $targetEventId,
                ]);
            }
        }
    }
}