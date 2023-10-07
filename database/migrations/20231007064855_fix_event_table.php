<?php

use think\migration\Migrator;
use think\migration\db\Column;

class FixEventTable extends Migrator
{
    public function up()
    {
        $table = $this->table('event', ['engine' => 'MyISAM']);

        if (!$table->hasColumn('max_year')) {
            $table->addColumn('max_year', 'integer', ['default' => 0, 'comment' => '发生年份'])
                ->save();
        }
        if (!$table->hasColumn('min_year')) {
            $table->addColumn('min_year', 'integer', ['default' => 0, 'comment' => '发生年份'])
                ->save();
        }
    }
}
