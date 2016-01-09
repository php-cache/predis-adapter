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

use Cache\Adapter\Predis\PredisCachePool;
use Cache\IntegrationTests\TaggableCachePoolTest;
use Predis\Client;

class IntegrationTagTest extends TaggableCachePoolTest
{
    use CreatePoolTrait;
}
