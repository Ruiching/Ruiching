<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateSubject extends Migrator
{
    public function up()
    {
        $table = $this->table('subject', ['engine' => 'MyISAM']);
        $table->addColumn('subject_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '事件ID'])
            ->addColumn('subject_type', 'string', ['limit' => 16, 'default' => '', 'comment' => '参与者类型:人物或机构'])
            ->addColumn('name', 'string', ['limit' => 64, 'default' => '', 'comment' => '姓名'])
            ->addColumn('gender', 'string', ['limit' => 8, 'default' => '', 'comment' => '性别'])
            ->addColumn('country', 'string', ['limit' => 32, 'default' => '', 'comment' => '国籍'])
            ->addColumn('birth_date', 'string', ['limit' => 32, 'default' => '', 'comment' => '出生日期'])
            ->addColumn('birth_date_timestamp', 'integer', ['default' => 0, 'comment' => '出生日期时间戳'])
            ->addColumn('death_date', 'string', ['limit' => 32, 'default' => '', 'comment' => '去世日期'])
            ->addColumn('death_date_timestamp', 'integer', ['default' => 0, 'comment' => '去世日期时间戳'])
            ->addColumn('career', 'string', ['limit' => 32, 'default' => '', 'comment' => '职业'])
            ->addColumn('archivement', 'text', ['null' => true, 'comment' => '主要成就'])
            ->addIndex('subject_id')
            ->create();


        $table = $this->table('event_subject', ['engine' => 'MyISAM']);
        $table->addColumn('event_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '事件ID'])
            ->addColumn('subject_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '参与者ID'])
            ->addIndex('event_id')
            ->addIndex('subject_id')
            ->create();

    }

    public function down()
    {
        $this->dropTable('subject');
        $this->dropTable('event_subject');
    }
}
