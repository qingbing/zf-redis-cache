<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace Zf\Cache;

use Zf\Cache\Abstracts\ACache;
use Zf\Helper\Exceptions\RuntimeException;

/**
 * @author      qingbing<780042175@qq.com>
 * @describe    Redis 缓存管理
 *
 * Class RedisCache
 * @package Zf\Cache
 */
class RedisCache extends ACache
{
    /**
     * @describe    redis的一个实例
     *
     * @var \Redis
     */
    public $redis;
    /**
     * @describe    redis的一个实例
     *
     * @var int
     */
    public $clearOnce = 1000;

    /**
     * @describe    属性赋值后执行函数
     *
     * @throws RuntimeException
     */
    public function init()
    {
        if (!$this->redis instanceof \Redis) {
            throw new RuntimeException('RedisCache必须指定redis实例');
        }
    }

    /**
     * @describe    获取缓存id
     *
     * @param mixed $key
     *
     * @return string
     */
    protected function buildId($key): string
    {
        return $this->namespace . '_' . md5((is_string($key) ? $key : json_encode($key)));
    }

    /**
     * @describe    通过缓存id获取信息
     *
     * @param string $id
     *
     * @return mixed
     */
    protected function getValue($id)
    {
        return $this->redis->get($id);
    }

    /**
     * @describe    设置缓存id的信息
     *
     * @param string $id
     * @param string $value
     * @param int $ttl
     *
     * @return bool
     */
    protected function setValue(string $id, string $value, $ttl): bool
    {
        return $this->redis->setex($id, $ttl, $value);
    }

    /**
     * @describe    删除缓存信息
     *
     * @param string $id
     *
     * @return bool
     */
    protected function deleteValue(string $id): bool
    {
        return $this->redis->del($id) > 0;
    }

    /**
     * @describe    清理当前命名空间的缓存
     *
     * @return bool
     */
    protected function clearValues(): bool
    {
        $kss = array_chunk($this->redis->keys($this->namespace . "_*"), $this->clearOnce);
        foreach ($kss as $keys) {
            $this->redis->del($keys);
        }
        return true;
    }

    /**
     * @describe    通过缓存ids获取信息
     *
     * @param array $ids
     *
     * @return array
     */
    protected function getMultiValue($ids)
    {
        return array_combine($ids, $this->redis->mget($ids));
    }

    /**
     * @describe    设置多个缓存
     *
     * @param mixed $kvs
     * @param null|int $ttl
     *
     * @return bool
     */
    protected function setMultiValue($kvs, $ttl = null): bool
    {
        // 批量设置
        $this->redis->mset($kvs);
        if ($ttl > 0) {
            // 单个设置有效期
            array_map(function ($key) use ($ttl) {
                $this->redis->expire($key, $ttl);
            }, array_keys($kvs));
        }
        return true;
    }

    /**
     * @describe    删除多个缓存
     *
     * @param array $ids
     *
     * @return bool
     */
    protected function deleteMultiValue($ids)
    {
        $this->redis->del($ids);
        return true;
    }

    /**
     * @describe    判断缓存是否存在
     *
     * @param string $id
     *
     * @return bool
     */
    protected function exist(string $id): bool
    {
        return $this->redis->exists($id);
    }
}