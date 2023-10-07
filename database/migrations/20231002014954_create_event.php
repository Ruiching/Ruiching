<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateEvent extends Migrator
{
    public function up()
    {
        $table = $this->table('event', ['engine' => 'MyISAM']);
        $table->addColumn('event_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '事件ID'])
            ->addColumn('name', 'string', ['limit' => 256, 'default' => '', 'comment' => '名称'])
            ->addColumn('action', 'string', ['limit' => 16, 'default' => '', 'comment' => '动作'])
            ->addColumn('object_attributive', 'string', ['limit' => 128, 'default' => '', 'comment' => '事件对象描述'])
            ->addColumn('object', 'string', ['limit' => 128, 'default' => '', 'comment' => '对象'])
            ->addColumn('time', 'string', ['limit' => 32, 'default' => '', 'comment' => '发生时间'])
            ->addColumn('min_year', 'integer', ['default' => 0, 'comment' => '发生年份'])
            ->addColumn('max_year', 'integer', ['default' => 0, 'comment' => '发生年份'])
            ->addColumn('formated_time', 'string', ['limit' => 32, 'default' => '', 'comment' => '归一化后的发生时间,最粗精确到年份,最细精确到日期;'])
            ->addColumn('time_granularity', 'integer', ['default' => 0, 'comment' => '时间精确度,天(1),月(31),年(376)'])
            ->addColumn('timestamp', 'integer', ['default' => 0, 'comment' => '自定义时间戳,用于排序'])
            ->addColumn('location', 'string', ['limit' => 64, 'default' => '', 'comment' => '发生地点'])
            ->addColumn('formated_location_id', 'integer', ['default' => 0, 'comment' => '归一化后的发生地点ID'])
            ->addColumn('introduction', 'text', ['null' => true, 'comment' => '事件描述'])
            ->addColumn('signifiance', 'string', ['limit' => 512, 'default' => '', 'comment' => '事件的历史意义'])
            ->addColumn('importance', 'integer', ['default' => 0, 'comment' => '重要度'])
            ->addColumn('quality_score', 'integer', ['default' => 0, 'comment' => '时间抽取质量评估分(内部使用)'])
            ->addColumn('debug_info', 'text', ['null' => true, 'comment' => '调试信息(内部使用)'])
            ->addIndex('event_id')
            ->create();

        $table = $this->table('raw_event', ['engine' => 'MyISAM']);
        $table->addColumn('event_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '事件ID'])
            ->addColumn('name', 'string', ['limit' => 256, 'default' => '', 'comment' => '名称'])
            ->addColumn('action', 'string', ['limit' => 16, 'default' => '', 'comment' => '动作'])
            ->addColumn('object', 'string', ['limit' => 128, 'default' => '', 'comment' => '对象'])
            ->addColumn('start_time', 'string', ['limit' => 32, 'default' => '', 'comment' => '发生时间'])
            ->addColumn('formated_start_time', 'integer', ['default' => 0, 'comment' => '归一化后的发生时间,最粗精确到年份,最细精确到日期'])
            ->addColumn('end_time', 'string', ['limit' => 32, 'default' => '', 'comment' => '最晚发生时间'])
            ->addColumn('formated_end_time', 'integer', ['default' => 0, 'comment' => '归一化后的最晚发生时间'])
            ->addColumn('location', 'string', ['limit' => 64, 'default' => '', 'comment' => '发生地点'])
            ->addColumn('formated_location_id', 'integer', ['default' => 0, 'comment' => '归一化后的发生地点ID'])
            ->addColumn('introduction', 'text', ['null' => true, 'comment' => '事件描述'])
            ->addColumn('signifiance', 'string', ['limit' => 512, 'default' => '', 'comment' => '事件的历史意义'])
            ->addColumn('quality_score', 'integer', ['default' => 0, 'comment' => '时间抽取质量评估分(内部使用)'])
            ->addColumn('debug_info', 'text', ['null' => true, 'comment' => '调试信息(内部使用)'])
            ->addIndex('event_id')
            ->create();

        $table = $this->table('event_relation', ['engine' => 'MyISAM']);
        $table->addColumn('relation_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '关系ID'])
            ->addColumn('source_event_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '源事件ID'])
            ->addColumn('target_event_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '目标事件ID'])
            ->addColumn('relation_type', 'string', ['limit' => 18, 'default' => '', 'comment' => '关系类型'])
            ->addIndex('relation_id')
            ->addIndex('source_event_id')
            ->addIndex('target_event_id')
            ->create();

        $table = $this->table('event_evolve_theme', ['engine' => 'MyISAM']);
        $table->addColumn('event_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '事件ID'])
            ->addColumn('theme', 'string', ['limit' => 64, 'default' => '', 'comment' => '演进主题'])
            ->addIndex('event_id')
            ->create();

        $table = $this->table('event_quality', ['engine' => 'MyISAM']);
        $table->addColumn('event_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '事件ID'])
            ->addColumn('ao_status', 'integer', ['default' => 0, 'comment' => '动宾关系检查结果'])
            ->addColumn('actual_status', 'integer', ['default' => 0, 'comment' => '是否真实事件检查结果'])
            ->addIndex('event_id')
            ->create();

        $table = $this->table('tag', ['engine' => 'MyISAM']);
        $table->addColumn('tag_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '标签ID'])
            ->addColumn('name', 'string', ['limit' => 32, 'default' => '', 'comment' => '标签名'])
            ->addColumn('type', 'string', ['limit' => 32, 'default' => '', 'comment' => '标签类型'])
            ->addIndex('tag_id')
            ->create();

        $table = $this->table('event_tag', ['engine' => 'MyISAM']);
        $table->addColumn('tag_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '标签ID'])
            ->addColumn('event_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '事件ID'])
            ->addIndex('tag_id')
            ->addIndex('event_id')
            ->create();

        $table = $this->table('formated_location', ['engine' => 'MyISAM']);
        $table->addColumn('location_id', 'string', ['limit' => 36, 'default' => '', 'comment' => 'ID'])
            ->addColumn('name', 'string', ['limit' => 32, 'default' => '', 'comment' => '名称'])
            ->addColumn('level', 'integer', ['default' => 0, 'comment' => '行政等级'])
            ->addColumn('full_name', 'string', ['limit' => 255, 'default' => '', 'comment' => '地址全名'])
            ->addColumn('parent_id', 'integer', ['default' => 0, 'comment' => '上级地名ID'])
            ->addColumn('start_year', 'integer', ['default' => 0, 'comment' => '适用历史起始年份'])
            ->addColumn('end_year', 'integer', ['default' => 0, 'comment' => '适用历史结束年份'])
            ->addIndex('location_id')
            ->create();

    }

    public function down()
    {
        $this->dropTable('event');
        $this->dropTable('raw_event');
        $this->dropTable('event_relation');
        $this->dropTable('event_evolve_theme');
        $this->dropTable('event_quality');
        $this->dropTable('tag');
        $this->dropTable('event_tag');
        $this->dropTable('formated_location');
    }
}
