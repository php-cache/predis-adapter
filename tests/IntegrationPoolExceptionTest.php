<?php

/*
 * This file is part of php-cache\predis-adapter package.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\Adapter\Predis\Tests;

use Cache\IntegrationTests\CachePoolTest;

class IntegrationPoolExceptionTest extends CachePoolTest
{
    use CreateInvalidPoolTrait;

    protected $skippedTests = [
        'testGetItemsEmpty' => 'getItems doesn\'t access the Redis sever.',
        'testGetItemInvalidKeys' => 'testGetItemInvalidKeys should throw \Cache\Adapter\Common\Exception\InvalidArgumentException.',
        'testHasItemInvalidKeys' => 'testHasItemInvalidKeys should throw \Cache\Adapter\Common\Exception\InvalidArgumentException.',
        'testDeleteItemInvalidKeys' => 'testDeleteItemInvalidKeys should throw \Cache\Adapter\Common\Exception\InvalidArgumentException.',
    ];

    protected function tearDown()
    {
        // do nothing, since we create an invalid cache pool
    }

    protected function assertPreConditions()
    {
        $this->setExpectedException('\Cache\Adapter\Predis\PredisCacheException');
    }

    public function testClear()
    {
        $this->createCachePool()->clear();
    }

    public function testSave()
    {
        $cacheItem = $this->getMock('\Psr\Cache\CacheItemInterface');
        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);
        $this->createCachePool()->save($cacheItem);
    }
}
