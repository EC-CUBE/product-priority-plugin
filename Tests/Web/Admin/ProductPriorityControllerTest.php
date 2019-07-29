<?php
/*
  * This file is part of EC-CUBE
  *
  * Copyright (C) EC-CUBE CO.,LTD. All Rights Reserved.
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Plugin\ProductPriority\Tests\Web\Admin;

use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;

class ProductPriorityControllerTest extends AbstractAdminWebTestCase
{
    public function testRouting()
    {
        $this->client->request('GET', $this->app->url('admin_product_priority'));
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->client->request('GET', $this->app->url('admin_product_priority_edit', array(
            'categoryId' => 0,
        )));
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->client->request('GET', $this->app->url('admin_product_priority_edit', array(
            'categoryId' => 1,
        )));
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }
}
