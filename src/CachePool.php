<?php

/*
 * This file is part of php-cache\doctrine-adapter package.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\Adapter\Redis;

use Cache\Adapter\Redis\Exception\InvalidArgumentException;
use Cache\Taggable\TaggableItemInterface;
use Cache\Taggable\TaggablePoolInterface;
use Cache\Taggable\TaggablePoolTrait;
use Predis\Client;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * This is a bridge between PSR-6 and aDoctrine cache.
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CachePool implements CacheItemPoolInterface, TaggablePoolInterface
{
    use TaggablePoolTrait;

    /**
     * List of invalid (or reserved) key characters.
     *
     * @var string
     */
    const KEY_INVALID_CHARACTERS = '{}()/\@:';

    /**
     * @var Client
     */
    private $cache;

    /**
     * @var CacheItemInterface[] deferred
     */
    private $deferred = [];

    /**
     * @param Client $cache
     */
    public function __construct(Client $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Make sure to commit before we destruct.
     */
    public function __destruct()
    {
        $this->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key, array $tags = [])
    {
        $this->validateKey($key);
        $taggedKey = $this->generateCacheKey($key, $tags);

        return $this->getItemWithoutGenerateCacheKey($taggedKey);
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemWithoutGenerateCacheKey($key)
    {
        if (isset($this->deferred[$key])) {
            $item = $this->deferred[$key];

            return is_object($item) ? clone $item : $item;
        }

        $item = unserialize($this->cache->get($key));
        if (false === $item || !$item instanceof CacheItemInterface) {
            $item = new CacheItem($key);
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [], array $tags = [])
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key, $tags);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key, array $tags = [])
    {
        return $this->getItem($key, $tags)->isHit();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(array $tags = [])
    {
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $this->flushTag($tag);
            }

            return true;
        }

        // Clear the deferred items
        $this->deferred = [];

        return 'OK' === $this->cache->flushdb()->getPayload();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key, array $tags = [])
    {
        $this->validateKey($key);
        $taggedKey = $this->generateCacheKey($key, $tags);

        // Delete form deferred
        unset($this->deferred[$taggedKey]);

        // Delete form cache
        return $this->cache->del($taggedKey) >= 0;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys, array $tags = [])
    {
        $deleted = true;
        foreach ($keys as $key) {
            if (!$this->deleteItem($key, $tags)) {
                $deleted = false;
            }
        }

        return $deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        if ($item instanceof TaggableItemInterface) {
            $key = $item->getTaggedKey();
        } else {
            $key = $item->getKey();
        }

        if ($item instanceof HasExpirationDateInterface) {
            if (null !== $expirationDate = $item->getExpirationDate()) {
                $timeToLive = $expirationDate->getTimestamp() - time();

                return 'OK' === $this->cache->setex($key, $timeToLive, serialize($item))->getPayload();
            }
        }

        return 'OK' === $this->cache->set($key, serialize($item))->getPayload();
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        if ($item instanceof TaggableItemInterface) {
            $key = $item->getTaggedKey();
        } else {
            $key = $item->getKey();
        }

        $this->deferred[$key] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $saved = true;
        foreach ($this->deferred as $item) {
            if (!$this->save($item)) {
                $saved = false;
            }
        }
        $this->deferred = [];

        return $saved;
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    private function validateKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(sprintf(
                'Cache key must be string, "%s" given', gettype($key)
            ));
        }

        $invalid = preg_quote(static::KEY_INVALID_CHARACTERS, '|');
        if (preg_match('|['.$invalid.']|', $key)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid key: "%s". The key contains one or more characters reserved for future extension: %s',
                $key,
                static::KEY_INVALID_CHARACTERS
            ));
        }
    }

    /**
     * @param string $name
     *
     * @throws InvalidArgumentException
     */
    protected function validateTagName($name)
    {
        $this->validateKey($name);
    }
}
