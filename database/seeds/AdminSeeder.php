<?php

use think\migration\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $this->table('admin')->insert([
            'username' => 'admin',
            'nickname' => '超管',
            'password' => password_hash('admin123', 1),
            'created_at' => time(),
            'updated_at' => time(),
        ])->save();

        $this->table('admin_role')->insert([
            'name' => 'Super Admin',
            'nickname' => '超级管理员',
            'perms' => '',
            'is_super' => 1,
            'is_system' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ])->save();

        $this->table('admin_role_map')->insert([
            'role_id' => 1,
            'admin_id' => 1,
        ])->save();
    }
}