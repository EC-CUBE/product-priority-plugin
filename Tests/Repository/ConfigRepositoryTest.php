<?php
/*
  * This file is part of EC-CUBE
  *
  * Copyright (C) EC-CUBE CO.,LTD. All Rights Reserved.
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Plugin\ProductPriority\Tests\Repository;

use Eccube\Tests\EccubeTestCase;

class ConfigRepositoryTest extends EccubeTestCase
{
    public function testFind()
    {
        $Entity = $this->app['eccube.plugin.product_priority.repository.config']->find(1);

        $this->assertInstanceOf('Plugin\ProductPriority\Entity\Config', $Entity);
    }
}
