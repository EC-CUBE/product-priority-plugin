<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 * https://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Plugin\ProductPriority;

use Eccube\Application;
use Eccube\Entity\Category;
use Eccube\Entity\Product;
use Eccube\Event\EventArgs;
use Plugin\ProductPriority\Entity\Config;

class ProductPriorityEvent
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * ProductPriorityEvent constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 商品一覧画面おすすめ順ソート.
     *
     * @param EventArgs $event
     */
    public function onProductIndexSearch(EventArgs $event)
    {
        $searchData = $event->getArgument('searchData');

        $Config = $this->app['eccube.plugin.product_priority.repository.config']
            ->find(Config::ID);

        if (!(isset($searchData['orderby']) && $searchData['orderby']->getId() == $Config->getOrderById())) {
            return;
        }

        $qb = $event->getArgument('qb');

        $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->buildSortQuery($qb, $searchData['category_id']);
    }

    /**
     * 商品編集時, 商品並び順テーブルに登録されているカテゴリとの整合性を保つ.
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditComplete(EventArgs $event)
    {
        /** @var Product $Product */
        $Product = $event->getArgument('Product');

        $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->cleanupProductPriority($Product);
    }

    /**
     * 商品が削除された場合, 商品並び順テーブルに登録されているデータを削除する.
     *
     * @param EventArgs $event
     */
    public function onAdminProductDeleteComplete(EventArgs $event)
    {
        /** @var Product $Product */
        $Product = $event->getArgument('Product');

        $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->deleteProductPriorityByProductId($Product->getId());
    }

    /**
     * カテゴリが削除された場合, 商品並び順テーブルに登録されているデータを削除する.
     *
     * @param EventArgs $event
     */
    public function onAdminProductCategoryDeleteComplete(EventArgs $event)
    {
        /** @var Category $Category */
        $Category = $event->getArgument('TargetCategory');

        $this->app['eccube.plugin.product_priority.repository.product_priority']
            ->deleteProductPriorityByCategoryId($Category->getId());
    }
}
