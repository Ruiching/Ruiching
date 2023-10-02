<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateField extends Migrator
{
    public function up()
    {
        $table = $this->table('field', ['engine' => 'MyISAM']);
        $table->addColumn('field_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '学科ID'])
            ->addColumn('level_0_name', 'string', ['limit' => 32, 'default' => '', 'comment' => '0级学科名称'])
            ->addColumn('level_1_name', 'string', ['limit' => 32, 'default' => '', 'comment' => '1级学科名称'])
            ->addColumn('level_2_name', 'string', ['limit' => 32, 'default' => '', 'comment' => '2级学科名称'])
            ->addIndex('field_id')
            ->create();


        $table = $this->table('event_field', ['engine' => 'MyISAM']);
        $table->addColumn('event_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '事件ID'])
            ->addColumn('full_name', 'string', ['limit' => 64, 'default' => '', 'comment' => '学科全称'])
            ->addColumn('level_0_name', 'string', ['limit' => 32, 'default' => '', 'comment' => '0级学科名称'])
            ->addColumn('level_1_name', 'string', ['limit' => 32, 'default' => '', 'comment' => '1级学科名称'])
            ->addColumn('level_2_name', 'string', ['limit' => 32, 'default' => '', 'comment' => '2级学科名称'])
            ->addIndex('event_id')
            ->create();

    }

    public function down()
    {
        $this->dropTable('field');
        $this->dropTable('event_field');
    }
}
