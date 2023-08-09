<?php

namespace app\repository;

use Pimple\Container;

/**
 * Class BaseRepository
 *
 * @property \app\model\Admin $adminModel
 * @property \app\model\AdminRole $adminRoleModel
 * @property \app\model\AdminRoleMap $adminRoleMapModel
 * @property \app\model\HandleLog $handleLogModel
 * @property \app\model\Configs $configsModel
 *
 */
class BaseRepository extends Container
{
    const STATUS_ING = 0; //审核中
    const STATUS_SUCCESS = 1; //成功
    const STATUS_ERROR = 2; //失败

    const REMOTE_UN = 0; //未处理
    const REMOTE_IMG = 1; //提交中
    const REMOTE_SUCCESS = 2; //成功
    const REMOTE_ERROR = 3;  //失败


    protected $errorMsg = '操作失败';
    protected $errorCode = 110;

    protected $providers = [
        ModelProvider::class,
    ];

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->registerProviders();
    }

    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function addProvider($provider)
    {
        array_push($this->providers, $provider);
        return $this;
    }

    public function setProviders($providers)
    {
        $this->providers = [];
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    protected function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

}