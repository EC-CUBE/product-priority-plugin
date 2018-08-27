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

namespace Plugin\ProductPriority\Tests\Web\HookPoint;

use Eccube\Entity\Master\ProductListOrderBy;
use Eccube\Repository\Master\ProductListOrderByRepository;
use Eccube\Tests\Web\AbstractWebTestCase;
use Plugin\ProductPriority\Entity\Config;
use Plugin\ProductPriority\Repository\ConfigRepository;
use Plugin\ProductPriority\Repository\ProductPriorityRepository;

class OnProductIndexSearchTest extends AbstractWebTestCase
{
    /**
     * @var ProductPriorityRepository
     */
    private $productPriorityRepos;

    /**
     * @var ConfigRepository
     */
    private $configRepo;

    private $sortNo;

    public function setUp()
    {
        parent::setUp();

        $this->productPriorityRepos = $this->container->get(ProductPriorityRepository::class);
        $this->configRepo = $this->container->get(ConfigRepository::class);
        /** @var Config $Config */
        $Config = $this->configRepo->find(Config::ID);
        /** @var ProductListOrderBy $productListOrderBy */
        $productListOrderBy = $this->container->get(ProductListOrderByRepository::class)->find($Config->getOrderById());
        $this->sortNo = $productListOrderBy->getSortNo();
    }

    public function testOnProductIndexSearch()
    {
        $this->client->request('GET', $this->generateUrl('product_list'), ['orderby' => $this->sortNo]);
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testOnProductIndexSearchWithCategory()
    {
        $this->client->request(
            'GET',
            $this->generateUrl('product_list'),
            ['category_id' => 1, 'orderby' => $this->sortNo]
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }
}
