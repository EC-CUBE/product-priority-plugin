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

class OnAdminProductEditCompleteTest extends AbstractAdminWebTestCase
{
    public function createFormData()
    {
        $faker = $this->getFaker();
        $form = array(
            'class' => array(
                'product_type' => 1,
                'price01' => $faker->randomNumber(5),
                'price02' => $faker->randomNumber(5),
                'stock' => $faker->randomNumber(3),
                'stock_unlimited' => 0,
                'code' => $faker->word,
                'sale_limit' => null,
                'delivery_date' => '',
            ),
            'name' => $faker->word,
            'product_image' => null,
            'description_detail' => $faker->text,
            'description_list' => $faker->paragraph,
            'Category' => null,
            'Tag' => 1,
            'search_word' => $faker->word,
            'free_area' => $faker->text,
            'Status' => 1,
            'note' => $faker->text,
            'tags' => null,
            'images' => null,
            'add_images' => null,
            'delete_images' => null,
            '_token' => 'dummy',
        );

        return $form;
    }

    public function testOnAdminProductEditComplete()
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

        $formData = $this->createFormData();
        $formData['Category'] = null; // カテゴリを空にして登録する

        $crawler = $this->client->request(
            'POST',
            $this->app->url('admin_product_product_edit', array('id' => $Product->getId())),
            array('admin_product' => $formData)
        );

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(
                $this->app->url('admin_product_product_edit', array('id' => $Product->getId()))
            )
        );

        $ProductPriorities = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->findBy(array('product_id' => $Product->getId()));

        $this->expected = 0;
        $this->actual = count($ProductPriorities);
        $this->verify('削除されているはず');
    }
}
