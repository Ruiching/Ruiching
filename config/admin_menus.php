<?php

return [
    [
        'name' => '管理',
        'icon' => 'fa fa-fw fa-home',
        'url' => 'index',
        'children' => [
            [
                'name' => '首页',
                'icon' => 'si si-speedometer',
                'url' => '',
                'children' => [
                    [
                        'name' => '项目统计',
                        'icon' => '',
                        'url' => 'index/index',
                    ],
                ],
            ],
        ]
    ],
    [
        'name' => '系统',
        'icon' => 'si si-settings',
        'url' => 'system',
        'children' => [
            [
                'name' => '系统设置',
                'icon' => 'fa fa-fw fa-cog',
                'url' => '',
                'children' => [
                    [
                        'name' => '基础设置',
                        'icon' => '',
                        'url' => 'config/base',
                    ],
                ]
            ],
            [
                'name' => '权限管理',
                'icon' => 'fa fa-fw fa-lock',
                'url' => 'perm',
                'children' => [
                    [
                        'name' => '角色管理',
                        'icon' => '',
                        'url' => 'perm/role',
                        'hide_children' => 1,
                        'children' => [
                            [
                                'name' => '新增',
                                'icon' => '',
                                'url' => 'perm/roleCreate'
                            ],
                            [
                                'name' => '编辑',
                                'icon' => '',
                                'url' => 'perm/roleUpdate'
                            ],
                            [
                                'name' => '删除',
                                'icon' => '',
                                'url' => 'perm/roleDestroy'
                            ],
                            [
                                'name' => '状态',
                                'icon' => '',
                                'url' => 'perm/roleSingleUpdate'
                            ],
                        ]
                    ],
                    [
                        'name' => '管理员管理',
                        'icon' => '',
                        'url' => 'perm/user',
                        'hide_children' => 1,
                        'children' => [
                            [
                                'name' => '新增',
                                'icon' => '',
                                'url' => 'perm/userCreate'
                            ],
                            [
                                'name' => '编辑',
                                'icon' => '',
                                'url' => 'perm/userUpdate'
                            ],
                            [
                                'name' => '删除',
                                'icon' => '',
                                'url' => 'perm/userDestroy'
                            ],
                            [
                                'name' => '状态',
                                'icon' => '',
                                'url' => 'perm/userSingleUpdate'
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => '系统日志',
                'icon' => 'fa fa-fw fa-clipboard',
                'url' => 'handle/index',
            ],
        ]
    ],
];