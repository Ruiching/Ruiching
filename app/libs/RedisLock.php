<?php

namespace app\libs;

class RedisLock
{
    private $redis;
    #存储redis对象

    /**
     * @desc 构造函数
     */
    public function __construct()
    {
        $this->redis = redis_server();
    }

    /**
     * @desc 加锁方法
     *
     * @param $lockName string 锁的名字
     * @param $timeout int 锁的过期时间
     * @return
     */
    public function getLock(string $lockName, $timeout = 10)
    {
        $identifier = uniqid();
        #获取唯一标识符
        $timeout = intval($timeout);
        #确保是整数
        $end = time() + $timeout;
        #循环获取锁
        while (time() < $end) {
            #查看$lockName是否被上锁,为$lockName设置过期时间，防止死锁
            if ($this->redis->set($lockName, $identifier, array('nx', 'ex' => $timeout))) {
                return $identifier;
            }
            usleep(0.001);
            #停止0.001ms
        }
        return false;
    }

    /**
     * @desc 释放锁
     *
     * @param $lockName string 锁名
     * @param $identifier string 锁的唯一值
     * @return bool
     */
    public function releaseLock(string $lockName, string $identifier)
    {
        // 判断是锁有没有被其他客户端修改
        if ($this->redis->get($lockName) == $identifier) {
            $this->redis->multi();
            $this->redis->del($lockName);
            #释放锁
            $this->redis->exec();
            return true;
        } else {
            return false;
            #其他客户端修改了锁，不能删除别人的锁
        }
    }
}