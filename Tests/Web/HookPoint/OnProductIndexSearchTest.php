<?php
/*
  * This file is part of EC-CUBE
  *
  * Copyright (C) EC-CUBE CO.,LTD. All Rights Reserved.
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Plugin\ProductPriority\Tests\Web\HookPoint;

use Eccube\Tests\Web\AbstractWebTestCase;
use Plugin\ProductPriority\Entity\Config;

class OnProductIndexSearchTest extends AbstractWebTestCase
{
    public function testOnProductIndexSearch()
    {
        $Config = $this->app['eccube.plugin.product_priority.repository.config']
            ->find(Config::ID);

        $this->client->request('GET', $this->app->url('product_list'), array('orderby' => $Config->getOrderById()));
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testOnProductIndexSearchWithCategory()
    {
        $Config = $this->app['eccube.plugin.product_priority.repository.config']
            ->find(Config::ID);

        $this->client->request(
            'GET',
            $this->app->url('product_list'),
            array('category_id' => 1, 'orderby' => $Config->getOrderById())
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }
}
