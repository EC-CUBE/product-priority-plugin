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

class OnAdminProductCategoryDeleteCompleteTest extends AbstractAdminWebTestCase
{
    public function testOnAdminProductCategoryDeleteComplete()
    {
        $Member = $this->createMember();
        $Category = $this->createCategory($Member);
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
            ->findBy(array('category_id' => $Category->getId()));

        $this->assertGreaterThanOrEqual(1, count($ProductPriorities), '1件以上登録されている');

        $this->client->request(
            'DELETE',
            $this->app->url(
                'admin_product_category_delete',
                array('id' => $Category->getId())
            )
        );
        $this->assertTrue($this->client->getResponse()->isRedirect($this->app->url('admin_product_category')));

        $ProductPriorities = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->findBy(array('category_id' => $Category->getId()));

        $this->expected = 0;
        $this->actual = count($ProductPriorities);
        $this->verify('削除されているはず');
    }

    protected function createCategory($Creator)
    {
        $Category = new \Eccube\Entity\Category();
        $Category->setName('テスト家具')
            ->setRank(100)
            ->setLevel(100)
            ->setDelFlg(false)
            ->setParent(null)
            ->setCreator($Creator);

        $this->app['orm.em']->persist($Category);
        $this->app['orm.em']->flush($Category);

        return $Category;
    }
}
