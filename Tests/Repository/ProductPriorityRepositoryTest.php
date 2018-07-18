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

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Entity\Product;
use Eccube\Repository\ProductRepository;
use Eccube\Tests\EccubeTestCase;
use Plugin\ProductPriority\Entity\ProductPriority;
use Plugin\ProductPriority\Repository\ProductPriorityRepository;

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

    /**
     * @var ProductPriorityRepository
     */
    private $productPriorityRepository;

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
            $this->entityManager->persist($ProductPriority);
            $this->entityManager->flush($ProductPriority);

            $this->ProductPriorities->add($ProductPriority);
        }

        $this->productPriorityRepository = $this->container->get(ProductPriorityRepository::class);
    }

    public function testFind()
    {
        $ProductPriority = $this->ProductPriorities[0];
        $entity = $this->productPriorityRepository
            ->find(
                [
                    'product_id' => $ProductPriority->getProductId(),
                    'category_id' => $ProductPriority->getCategoryId(),
                ]
            );

        $this->assertInstanceOf('Plugin\ProductPriority\Entity\ProductPriority', $entity);
    }

    public function testGetPriorityCountGroupByCategory()
    {
        $array = $this->productPriorityRepository
            ->getPriorityCountGroupByCategory();

        /** @var ProductPriority $productPriority */
        foreach ($this->ProductPriorities as $productPriority) {
            $this->assertArrayHasKey($productPriority->getCategoryId(), $array);
        }
    }

    public function testGetMaxPriorityByCategoryId()
    {
        $max = $this->productPriorityRepository
            ->getMaxPriorityByCategoryId(1);

        $this->assertEquals(1, $max);
    }

    public function testGetPrioritiesByCategoryAsArray()
    {
        $array = $this->productPriorityRepository
            ->getPrioritiesByCategoryAsArray(null);

        $this->assertCount(0, $array);
    }

    public function testGetProductQueryBuilder()
    {
        $qb = $this->productPriorityRepository
            ->getProductQueryBuilder(null, null);

        $this->assertEquals(count($this->container->get(ProductRepository::class)->findBy([])), count($qb->getQuery()->getResult()));
    }

    public function testCleanupProductPriority()
    {
        $this->productPriorityRepository
            ->cleanupProductPriority($this->Product);

        $this->assertEquals(count($this->ProductPriorities), count($this->productPriorityRepository->findBy([])));
    }

    public function testBuildSortQuery()
    {
        $qb = $this->container->get(ProductRepository::class)->getQueryBuilderBySearchData([]);
        $this->productPriorityRepository->buildSortQuery($qb);

        $this->assertEquals(count($this->container->get(ProductRepository::class)->findBy([])), count($qb->getQuery()->getResult()));
    }

    public function testDeleteProductPriorityByProductId()
    {
        $this->productPriorityRepository
            ->deleteProductPriorityByProductId($this->Product->getId());
        $this->assertEquals(0, count($this->productPriorityRepository->findBy(['product_id' => $this->Product->getId()])));
    }

    public function testDeleteProductPriorityByCategoryId()
    {
        $categoryId = $this->Product->getProductCategories()->first()->getCategory()->getId();
        $this->productPriorityRepository
            ->deleteProductPriorityByCategoryId($categoryId);

        $this->assertEquals(0, count($this->productPriorityRepository->findBy(['category_id' => $categoryId])));
    }

    public function testCountProductCategory()
    {
        foreach ($this->Product->getProductCategories() as $ProductCategory) {
            $count = $this->productPriorityRepository
                ->countProductCategory($ProductCategory->getProductId(), $ProductCategory->getCategoryId());

            $this->assertGreaterThan(0, $count);
        }

        // 存在しないProductCategoryのチェック
        $count = $this->productPriorityRepository
            ->countProductCategory(999, 999);

        $this->assertEquals(0, $count);
    }
}
