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

use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Plugin\ProductPriority\Entity\ProductPriority;

class OnAdminProductDeleteCompleteTest extends AbstractAdminWebTestCase
{
    public function testOnAdminProductDeleteComplete()
    {
        $Product = $this->createProduct(null, 0);
        $i = 1;
        foreach ($Product->getProductCategories() as $ProductCategory) {
            $ProductPriority = new ProductPriority();
            $ProductPriority->setProductId($Product->getId());
            $ProductPriority->setCategoryId($ProductCategory->getCategory()->getId());
            $ProductPriority->setPriority($i++);
            $this->app['orm.em']->persist($ProductPriority);
            $this->app['orm.em']->flush($ProductPriority);
        }

        $ProductPriorities = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->findBy(array('product_id' => $Product->getId()));

        $this->assertGreaterThanOrEqual(1, count($ProductPriorities), '1件以上登録されている');

        $crawler = $this->client->request(
            'DELETE',
            $this->app->url('admin_product_product_delete', array('id' => $Product->getId()))
        );

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(
                $this->app->url('admin_product_page', array('page_no' => 1)).'?resume=1'
            )
        );

        $ProductPriorities = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->findBy(array('product_id' => $Product->getId()));

        $this->expected = 0;
        $this->actual = count($ProductPriorities);
        $this->verify('削除されているはず');
    }
}
