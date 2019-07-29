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

class ConfigControllerTest extends AbstractAdminWebTestCase
{
    public function testRouting()
    {
        $this->client->request('GET', $this->app->url('plugin_ProductPriority_config'));
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testSubmit()
    {
        $this->client->request(
            'POST',
            $this->app->url('plugin_ProductPriority_config'),
            array(
                'admin_product_priority_config' => array(
                    '_token' => 'dummy',
                    'order_by_id' => 10,
                ),
            )
        );

        $this->expected = true;
        $this->actual = $this->client->getResponse()->isRedirection();
        $this->verify();
    }
}
