<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateConfig extends Migrator
{
    public function up()
    {
        $configTable = $this->table('configs', ['engine' => 'MyISAM']);
        $configTable->addColumn('type', 'string', ['default' => 'base', 'limit' => 50 ,'comment' => '设置类型: base 基本设置'])
            ->addColumn('key', 'string', ['default' => '', 'comment' => 'KEY'])
            ->addColumn('value', 'text', ['null' => true, 'comment' => 'Value'])
            ->addIndex('type')
            ->addIndex('key')
            ->create();
    }

    public function down()
    {
        $this->dropTable('configs');
    }
}
