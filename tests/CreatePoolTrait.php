<?php

namespace Cache\Adapter\Predis\Tests;

use Cache\Adapter\Predis\PredisCachePool;
use Predis\Client;

trait CreatePoolTrait
{
    private $client = null;

    public function createCachePool()
    {
        return new PredisCachePool($this->getClient());
    }

    private function getClient()
    {
        if ($this->client === null) {
            $this->client = new Client('tcp:/127.0.0.1:6379');
        }

        return $this->client;
    }
}