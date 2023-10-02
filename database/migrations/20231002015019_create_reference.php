<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateReference extends Migrator
{
    public function up()
    {
        $table = $this->table('literature', ['engine' => 'MyISAM']);
        $table->addColumn('literature_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '文献ID'])
            ->addColumn('type', 'string', ['limit' => 32, 'default' => '', 'comment' => '文献类型'])
            ->addColumn('title', 'string', ['limit' => 64, 'default' => '', 'comment' => '名称'])
            ->addColumn('url', 'string', ['limit' => 256, 'default' => '', 'comment' => 'URL'])
            ->addIndex('literature_id')
            ->create();

        $table = $this->table('literature_textual_content', ['engine' => 'MyISAM']);
        $table->addColumn('literature_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '文献ID'])
            ->addColumn('page_num', 'integer', ['default' => 0, 'comment' => '页数'])
            ->addColumn('text', 'text', ['null' => true, 'comment' => '文本内容'])
            ->addIndex('literature_id')
            ->create();

        $table = $this->table('reference', ['engine' => 'MyISAM']);
        $table->addColumn('reference_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '文本片段ID'])
            ->addColumn('text', 'text', ['null' => true, 'comment' => '文本内容'])
            ->addColumn('literature_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '来源文献ID'])
            ->addColumn('page_no', 'integer', ['default' => 0, 'comment' => '所在文献的页数'])
            ->addColumn('paragraph_index', 'integer', ['default' => 0, 'comment' => '所在页面中的第几个段落'])
            ->addColumn('ner_result', 'text', ['null' => true, 'comment' => '实体识别结果'])
            ->addIndex('reference_id')
            ->addIndex('literature_id')
            ->create();

        $table = $this->table('event_reference', ['engine' => 'MyISAM']);
        $table->addColumn('event_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '事件ID'])
            ->addColumn('reference_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '文献ID'])
            ->addColumn('related_props', 'string', ['limit' => 64, 'default' => '', 'comment' => '关联时间属性,属性之间以‘,’分割'])
            ->addIndex('event_id')
            ->addIndex('reference_id')
            ->create();

        $table = $this->table('event_relation_reference', ['engine' => 'MyISAM']);
        $table->addColumn('relation_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '事件关系ID'])
            ->addColumn('reference_id', 'string', ['limit' => 36, 'default' => '', 'comment' => '参考文本片段ID'])
            ->addIndex('relation_id')
            ->addIndex('reference_id')
            ->create();

    }

    public function down()
    {
        $this->dropTable('literature');
        $this->dropTable('literature_textual_content');
        $this->dropTable('reference');
        $this->dropTable('event_reference');
        $this->dropTable('event_relation_reference');
    }
}
