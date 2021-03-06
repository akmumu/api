<?php

namespace Dingo\Api\Tests\Routing\Adapter;

use Illuminate\Routing\Router;
use Illuminate\Events\Dispatcher;
use Dingo\Api\Routing\Adapter\Laravel;

class LaravelTest extends BaseAdapterTest
{
    public function getAdapterInstance()
    {
        return new Laravel(new Router(new Dispatcher, $this->container));
    }
}
