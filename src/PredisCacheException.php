<?php

namespace Cache\Adapter\Predis;

use Predis\PredisException;
use Psr\Cache\CacheException;

class PredisCacheException extends PredisException implements CacheException
{
}
