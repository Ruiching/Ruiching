<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateHandleLog extends Migrator
{
    public function up()
    {
        $logTable = $this->table('handle_log', ['engine' => 'MyISAM']);
        $logTable->addColumn('admin_id', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '管理员ID'])
            ->addColumn('handle', 'string', ['default' => '', 'comment' => '处理说明'])
            ->addColumn('record', 'text', ['null' => true, 'comment' => '处理内部记录'])
            ->addColumn('action', 'string', ['limit' => 60, 'default' => '', 'comment' => 'Module/Action'])
            ->addColumn('ip', 'string', ['limit' => 20, 'default' => '', 'comment' => 'IP'])
            ->addColumn('is_success', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '是否成功'])
            ->addColumn('created_at', 'integer', ['default' => 0, 'comment' => '创建时间'])
            ->addIndex('admin_id')
            ->addIndex('is_success')
            ->create();
    }

    public function down()
    {
        $this->dropTable('handle_log');
    }
}
