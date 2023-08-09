<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAdmin extends Migrator
{
    public function up()
    {
        $adminTable = $this->table('admin', ['engine' => 'MyISAM']);
        $adminTable->addColumn('username', 'string', ['limit' => 20, 'default' => '', 'comment' => '用户名'])
            ->addColumn('nickname', 'string', ['limit' => 40, 'default' => '', 'comment' => '昵称'])
            ->addColumn('password', 'string', ['limit' => 60, 'default' => '', 'comment' => '密码'])
            ->addColumn('is_seal', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否封禁'])
            ->addColumn('created_at', 'integer', ['default' => 0, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'integer', ['default' => 0, 'comment' => '修改时间'])
            ->create();

        $roleTable = $this->table('admin_role', ['engine' => 'MyISAM']);
        $roleTable->addColumn('name', 'string', ['limit' => 40, 'default' => '', 'comment' => '角色名称'])
            ->addColumn('nickname', 'string', ['limit' => 40, 'default' => '', 'comment' => '角色昵称'])
            ->addColumn('description', 'string', ['default' => '', 'comment' => '角色描述'])
            ->addColumn('perms', 'text', ['null' => true, 'comment' => '所拥有权限'])
            ->addColumn('sort', 'integer', ['limit' => 4, 'default' => 0, 'comment' => '排序值'])
            ->addColumn('is_super', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否超管'])
            ->addColumn('is_seal', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否禁用'])
            ->addColumn('is_system', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否系统'])
            ->addColumn('created_at', 'integer', ['default' => 0, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'integer', ['default' => 0, 'comment' => '修改时间'])
            ->create();

        $mapTable = $this->table('admin_role_map', ['engine' => 'MyISAM']);
        $mapTable->addColumn('role_id', 'integer', ['default' => 0, 'comment' => '角色ID'])
            ->addColumn('admin_id', 'integer', ['default' => 0, 'comment' => '管理员ID'])
            ->create();
    }

    public function down()
    {
        $this->dropTable('admin');
        $this->dropTable('admin_role');
        $this->dropTable('admin_role_map');
    }
}
