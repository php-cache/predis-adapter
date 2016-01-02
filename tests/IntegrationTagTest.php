<?php

/*
 * This file is part of php-cache\doctrine-adapter package.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\Doctrine\Tests;

use Cache\Doctrine\CachePool;
use Cache\IntegrationTests\TaggableCachePoolTest;
use Doctrine\Common\Cache\ArrayCache;

class IntegrationTagTest extends TaggableCachePoolTest
{
    private $doctrineCache = null;

    public function createCachePool()
    {
        return new CachePool($this->getDoctrineCache());
    }

    private function getDoctrineCache()
    {
        if ($this->doctrineCache === null) {
            $this->doctrineCache = new ArrayCache();
        }

        return $this->doctrineCache;
    }
}
