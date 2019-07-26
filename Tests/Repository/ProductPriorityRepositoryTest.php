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

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Entity\Product;
use Eccube\Tests\EccubeTestCase;
use Plugin\ProductPriority\Entity\ProductPriority;

class ProductPriorityRepositoryTest extends EccubeTestCase
{
    /**
     * @var Product
     */
    protected $Product;

    /**
     * @var ProductPriority
     */
    protected $ProductPriorities;

    public function setUp()
    {
        parent::setUp();

        $this->Product = $this->createProduct();
        $this->ProductPriorities = new ArrayCollection();
        $priority = 0;
        foreach ($this->Product->getProductCategories() as $ProductCateory) {
            $ProductPriority = new ProductPriority();
            $ProductPriority->setProductId($ProductCateory->getProduct()->getId());
            $ProductPriority->setCategoryId($ProductCateory->getCategory()->getId());
            $ProductPriority->setPriority(++$priority);
            $this->app['orm.em']->persist($ProductPriority);
            $this->app['orm.em']->flush($ProductPriority);

            $this->ProductPriorities->add($ProductPriority);
        }
    }

    public function testFind()
    {
        $ProductPriority = $this->ProductPriorities[0];
        $entity = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->find(
                array(
                    'product_id' => $ProductPriority->getProductId(),
                    'category_id' => $ProductPriority->getCategoryId(),
                )
            );

        $this->assertInstanceOf('Plugin\ProductPriority\Entity\ProductPriority', $entity);
    }

    public function testGetPriorityCountGroupByCategory()
    {
        $array = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->getPriorityCountGroupByCategory();
    }

    public function testGetMaxPriorityByCategoryId()
    {
        $max = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->getMaxPriorityByCategoryId(1);
    }

    public function testGetPrioritiesByCategoryAsArray()
    {
        $array = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->getPrioritiesByCategoryAsArray(null);
    }

    public function testGetProductQueryBuilder()
    {
        $qb = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->getProductQueryBuilder(null, null);
    }

    public function testCleanupProductPriority()
    {
        $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->cleanupProductPriority($this->Product);
    }

    public function testBuildSortQuery()
    {
        $qb = $this->app['eccube.repository.product']->getQueryBuilderBySearchData(array());
        $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->buildSortQuery($qb);
    }

    public function testDeleteProductPriorityByProductId()
    {
        $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->deleteProductPriorityByProductId(1);
    }

    public function testDeleteProductPriorityByCategoryId()
    {
        $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->deleteProductPriorityByCategoryId(1);
    }

    public function testCountProductCategory()
    {
        foreach ($this->Product->getProductCategories() as $ProductCategory) {
            $count = $this->app['eccube.plugin.product_priority.repository.product_priority']
                ->countProductCategory($ProductCategory->getProductId(), $ProductCategory->getCategoryId());

            $this->assertGreaterThan(0, $count);
        }

        // 存在しないProductCategoryのチェック
        $count = $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->countProductCategory(999, 999);

        $this->assertEquals(0, $count);
    }
}
