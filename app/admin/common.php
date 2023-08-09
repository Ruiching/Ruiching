<?php
// 这是系统自动生成的公共文件

use think\facade\Db;
use think\facade\Config;
use think\facade\Session;
use think\facade\Cache;

function get_admin_all_menus()
{
    return Config::get('admin_menus');
}

function get_admin_perms()
{
    $adminId = Session::get('admin_id');
    Cache::delete('admin-perms:' . $adminId);
    return Cache::remember('admin-perms:' . $adminId, function () use ($adminId) {
        $perms = [];
        $isSuper = false;
        $roleIds = Db::table('admin_role_map')->where(['admin_id' => $adminId])->column('role_id');
        $roles = Db::table('admin_role')->where('id','in', $roleIds)->select();
        foreach ($roles as $role) {
            if ($role['is_super']) {
                $isSuper = true;
            }
            $perms = array_merge($perms, explode(',', $role['perms']));
        }
        return [
            'is_super' => $isSuper,
            'perms' => array_unique($perms),
        ];
    }, 1800);
}

function get_admin_perm_menus()
{
    $menus = get_admin_all_menus();
    $perms = get_admin_perms();
    if (!$perms['is_super']) {
        foreach ($menus as $key => $menu) {
            if (!in_array($menu['url'], $perms['perms'])) {
                unset($menus[$key]);
            } else {
                foreach ($menu['children'] as $secondKey => $secondMenu) {
                    if (!in_array($secondMenu['url'], $perms['perms'])) {
                        unset($menu['children'][$secondKey]);
                    } else {
                        if (isset($secondMenu['children'])) {
                            foreach ($secondMenu['children'] as $thirdKey => $thirdMenu) {
                                if (!in_array($thirdMenu['url'], $perms['perms'])) {
                                    unset($secondMenu['children'][$thirdKey]);
                                } else {
                                    if (isset($thirdMenu['children'])) {
                                        foreach ($thirdMenu['children'] as $fourthKey => $fourthMenu) {
                                            if (!in_array($fourthMenu['url'], $perms['perms'])) {
                                                unset($thirdMenu['children'][$fourthKey]);
                                            }
                                            $thirdMenu['children'][$fourthKey] = $fourthMenu;
                                        }
                                    }
                                    $secondMenu['children'][$thirdKey] = $thirdMenu;
                                }
                            }
                        }
                        $menu['children'][$secondKey] = $secondMenu;
                    }
                }
                $menus[$key] = $menu;
            }
        }
    }
    return $menus;
}

function get_menus($menus)
{
    //大驼峰处理
    $currentUrl = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', request()->controller()) . '/' . request()->action());
    $currentUrl1 = strtolower(request()->controller() . '/' . request()->action());
    $topMenus = [];
    $sidebarMenus = [];
    $breadcrumbs = [];
    foreach ($menus as $menu) {
        $topMenu = [
            'name' => $menu['name'],
            'icon' => $menu['icon'],
            'url' => '',
        ];
        if(isset($menu['children'])){
            foreach ($menu['children'] as $secondKey => $secondMenu) {
                if (!isset($secondMenu['children']) || isset($secondMenu['hide_children'])) {
                    if (!$topMenu['url']) {
                        $topMenu['url'] = $secondMenu['url'];
                    }
                }
                if (strtolower($secondMenu['url']) == $currentUrl || strtolower($secondMenu['url']) == $currentUrl1) {
                    $topMenu['is_active'] = 1;
                    $menu['children'][$secondKey]['is_active'] = 1;
                    $sidebarMenus = $menu['children'];
                    $breadcrumbs[] = [
                        'name' => $menu['name'],
                        'url' => strpos($menu['url'], '/') !== false ? $menu['url'] : '',
                    ];
                    $breadcrumbs[] = [
                        'name' => $secondMenu['name'],
                        'url' => strpos($secondMenu['url'], '/') !== false ? $secondMenu['url'] : '',
                    ];
                } else {
                    if (isset($secondMenu['children'])) {
                        foreach ($secondMenu['children'] as $thirdKey => $thirdMenu) {
                            if (!$topMenu['url']) {
                                $topMenu['url'] = $thirdMenu['url'];
                            }
                            if (strtolower($thirdMenu['url']) == $currentUrl || strtolower($thirdMenu['url']) == $currentUrl1) {
                                $topMenu['is_active'] = 1;
                                $menu['children'][$secondKey]['is_active'] = 1;
                                $menu['children'][$secondKey]['children'][$thirdKey]['is_active'] = 1;
                                $sidebarMenus = $menu['children'];
                                $breadcrumbs[] = [
                                    'name' => $menu['name'],
                                    'url' => strpos($menu['url'], '/') !== false ? $menu['url'] : '',
                                ];
                                $breadcrumbs[] = [
                                    'name' => $secondMenu['name'],
                                    'url' => strpos($secondMenu['url'], '/') !== false ? $secondMenu['url'] : '',
                                ];
                                $breadcrumbs[] = [
                                    'name' => $thirdMenu['name'],
                                    'url' => strpos($thirdMenu['url'], '/') !== false ? $thirdMenu['url'] : '',
                                ];
                            }
                            if (isset($thirdMenu['children'])) {
                                foreach ($thirdMenu['children'] as $fourthKey => $fourthMenu) {
                                    if (strtolower($fourthMenu['url']) == $currentUrl || strtolower($fourthMenu['url']) == $currentUrl1) {
                                        $topMenu['is_active'] = 1;
                                        $menu['children'][$secondKey]['is_active'] = 1;
                                        $menu['children'][$secondKey]['children'][$thirdKey]['is_active'] = 1;
                                        $sidebarMenus = $menu['children'];
                                        $breadcrumbs = [];
                                        $breadcrumbs[] = [
                                            'name' => $menu['name'],
                                            'url' => strpos($menu['url'], '/') !== false ? $menu['url'] : '',
                                        ];
                                        $breadcrumbs[] = [
                                            'name' => $secondMenu['name'],
                                            'url' => strpos($secondMenu['url'], '/') !== false ? $secondMenu['url'] : '',
                                        ];
                                        $breadcrumbs[] = [
                                            'name' => $thirdMenu['name'],
                                            'url' => strpos($thirdMenu['url'], '/') !== false ? $thirdMenu['url'] : '',
                                        ];
                                        $breadcrumbs[] = [
                                            'name' => $fourthMenu['name'],
                                            'url' => strpos($fourthMenu['url'], '/') !== false ? $fourthMenu['url'] : '',
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $topMenus[] = $topMenu;
    }
    return [
        'top_menus' => $topMenus,
        'sidebar_menus' => $sidebarMenus,
        'breadcrumbs' => $breadcrumbs,
    ];
}

function get_admin_menus()
{
    $menus = get_admin_perm_menus();
    return get_menus($menus);
}

/**
 * 后台URL
 * @param $path
 * @param $parameters
 * @param $secure
 * @return string
 */
function admin_url($path, $parameters = [], $secure = null)
{
    $prefix = env('app.backend_prefix', 'admin');
    $url = $prefix . '/' . $path;
    return url($url, $parameters, $secure)->build();
}

/**
 * 操作记录
 *
 * @param string $handle 记录内容
 * @param array $recode
 * @param bool $isSuccess
 */
function handle_log($handle, $recode = [], $isSuccess = true)
{
    $admin = session('admin');
    if ($admin) {
        $handleLog = [
            'admin_id' => $admin->id,
            'handle' => $handle,
            'record' => json_encode($recode),
            'action' => request()->controller() . '/' . request()->action(),
            'is_success' => $isSuccess,
            'ip' => request()->ip(),
            'created_at' => time(),
        ];
        Db::table('handle_log')->insert($handleLog);
    }
}