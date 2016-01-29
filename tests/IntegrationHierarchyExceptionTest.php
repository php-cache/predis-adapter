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

use Cache\IntegrationTests\HierarchicalCachePoolTest;

class IntegrationHierarchyExceptionTest extends HierarchicalCachePoolTest
{
    use CreateInvalidPoolTrait;

    protected function tearDown()
    {
        // do nothing, since we create an invalid cache pool
    }

    protected function assertPreConditions()
    {
        $this->setExpectedException('\Cache\Adapter\Predis\PredisCacheException');
    }
}
