<?php

namespace app\admin\repository;

use app\repository\BaseRepository;
use app\repository\CommonTrait;
use think\facade\Cache;

class ConfigRepository extends BaseRepository
{
    use CommonTrait;

    /**
     * 获取系统配置
     * @param string $type
     * @return \think\Collection|\think\model\Collection
     */
    public function getConfig($type = 'base')
    {
        $list = $this->configsModel->where('type', $type)->select();
        $config = [];
        foreach ($list as $item) {
            $config[$item['key']] = $item->value;
        }
        return $this->configsModel->toCollection($config);
    }

    /**
     * 设置系统配置
     *
     * @param array $config
     * @param string $type
     * @return bool
     */
    public function setConfig($config, $type = 'base')
    {
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                $item = $this->configsModel->where(['key' => $key, 'type' => $type])->find();
                if ($item) {
                    $updateData = [
                        'key' => $key,
                        'value' => $value,
                    ];
                    $this->configsModel->where('id', $item['id'])->update($updateData);
                } else {
                    $insertData = [
                        'type' => $type,
                        'key' => $key,
                        'value' => $value,
                    ];
                    $this->configsModel->insert($insertData);
                }
            }
            $this->flushCache($type);
            return true;
        } else {
            return false;
        }
    }

    public function getConfigFormCache($type)
    {
        $configs = Cache::remember("web-configs-{$type}", function () use ($type) {
            return $this->getConfig($type);
        }, 1800);
        return $configs;
    }

    protected function flushCache($type)
    {
        $configs = $this->getConfig($type);
        Cache::store('base')->set("web-configs-{$type}", $configs, 1800);
    }

    public function setBase($data)
    {
        $config = [
            'wx_mini_app_id' => trim($data['wx_mini_app_id']),
            'wx_mini_app_secret' => trim($data['wx_mini_app_secret']),
            'wx_mini_env_version' => trim($data['wx_mini_env_version']),
            'wx_mini_check_path' => trim($data['wx_mini_check_path']),
            'wx_mini_start_page_url' => trim($data['wx_mini_start_page_url']),
            'wx_mini_photo_page_url' => trim($data['wx_mini_photo_page_url']),
            'ali_access_key_id' => trim($data['ali_access_key_id']),
            'ali_access_key_secret' => trim($data['ali_access_key_secret']),
            'ali_oss_bucket_name' => trim($data['ali_oss_bucket_name']),
            'ali_oss_endpoint' => trim($data['ali_oss_endpoint']),
            'ali_oss_domain_url' => trim($data['ali_oss_domain_url']),
            'device_start_qr_expired_sec' => trim($data['device_start_qr_expired_sec']),
            'mallcoo_app_id' => trim($data['mallcoo_app_id']),
            'mallcoo_public_key' => trim($data['mallcoo_public_key']),
            'mallcoo_private_key' => trim($data['mallcoo_private_key']),
        ];
        handle_log('修改 <系统配置-基本配置>', $config);
        return $this->setConfig($config);
    }

}