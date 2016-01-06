<?php

/*
 * This file is part of php-cache\redis-adapter package.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\Adapter\Redis;

use Cache\Adapter\Common\AbstractCachePool;
use Predis\Client;
use Psr\Cache\CacheItemInterface;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class RedisCachePool extends AbstractCachePool
{
    /**
     * @type Client
     */
    private $cache;

    /**
     * @param Client $cache
     */
    public function __construct(Client $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key, array $tags = [])
    {
        $this->validateKey($key);
        $taggedKey = $this->generateCacheKey($key, $tags);

        if (isset($this->deferred[$key])) {
            return true;
        }

        return $this->cache->exists($taggedKey);
    }

    protected function fetchObjectFromCache($key)
    {
        return unserialize($this->cache->get($key));
    }

    protected function clearAllObjectsFromCache()
    {
        return 'OK' === $this->cache->flushdb()->getPayload();
    }

    protected function clearOneObjectFromCache($key)
    {
        return $this->cache->del($key) >= 0;
    }

    protected function storeItemInCache($key, CacheItemInterface $item, $ttl)
    {
        if ($ttl === null) {
            return 'OK' === $this->cache->set($key, serialize($item))->getPayload();
        }

        return 'OK' === $this->cache->setex($key, $ttl, serialize($item))->getPayload();
    }
}
