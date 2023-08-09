<?php

namespace app\repository;

use app\model\Admin;
use app\model\AdminRole;
use app\model\AdminRoleMap;
use app\model\Configs;
use app\model\HandleLog;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ModelProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['adminModel'] = function ($pimple) {
            return new Admin();
        };
        $pimple['adminRoleModel'] = function ($pimple) {
            return new AdminRole();
        };
        $pimple['adminRoleMapModel'] = function ($pimple) {
            return new AdminRoleMap();
        };
        $pimple['configsModel'] = function ($pimple) {
            return new Configs();
        };
        $pimple['handleLogModel'] = function ($pimple) {
            return new HandleLog();
        };
    }
}