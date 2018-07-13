<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductPriority\Tests\Repository;

use Eccube\Tests\EccubeTestCase;
use Plugin\ProductPriority\Repository\ConfigRepository;

class ConfigRepositoryTest extends EccubeTestCase
{
    public function testFind()
    {
        $Entity = $this->container->get(ConfigRepository::class)->find(1);

        $this->assertInstanceOf('Plugin\ProductPriority\Entity\Config', $Entity);
    }
}
