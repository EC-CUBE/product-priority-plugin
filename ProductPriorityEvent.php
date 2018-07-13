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

namespace Plugin\ProductPriority;

use Eccube\Entity\Category;
use Eccube\Entity\Product;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\ProductPriority\Entity\Config;
use Plugin\ProductPriority\Repository\ConfigRepository;
use Plugin\ProductPriority\Repository\ProductPriorityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductPriorityEvent
 */
class ProductPriorityEvent implements EventSubscriberInterface
{
    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var ProductPriorityRepository
     */
    private $productPriorityRepository;

    /**
     * ProductPriorityEvent constructor.
     *
     * @param ConfigRepository $configRepository
     * @param ProductPriorityRepository $productPriorityRepository
     */
    public function __construct(ConfigRepository $configRepository, ProductPriorityRepository $productPriorityRepository)
    {
        $this->configRepository = $configRepository;
        $this->productPriorityRepository = $productPriorityRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EccubeEvents::FRONT_PRODUCT_INDEX_SEARCH => 'onProductIndexSearch',
            EccubeEvents::ADMIN_PRODUCT_EDIT_COMPLETE => 'onAdminProductEditComplete',
            EccubeEvents::ADMIN_PRODUCT_DELETE_COMPLETE => 'onAdminProductDeleteComplete',
            // Because the administrator can not delete a category when a category has a product, this event is no longer needed.
//            EccubeEvents::ADMIN_PRODUCT_CATEGORY_DELETE_COMPLETE => 'onAdminProductCategoryDeleteComplete'
        ];
    }

    /**
     * 商品一覧画面おすすめ順ソート.
     *
     * @param EventArgs $event
     */
    public function onProductIndexSearch(EventArgs $event)
    {
        $searchData = $event->getArgument('searchData');

        $Config = $this->configRepository->find(Config::ID);

        if (!(isset($searchData['orderby']) && $searchData['orderby']->getId() == $Config->getOrderById())) {
            return;
        }

        $qb = $event->getArgument('qb');

        $this->productPriorityRepository->buildSortQuery($qb, $searchData['category_id']);
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

        $this->productPriorityRepository->cleanupProductPriority($Product);
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

        $this->productPriorityRepository->deleteProductPriorityByProductId($Product->getId());
    }

    /**
     * カテゴリが削除された場合, 商品並び順テーブルに登録されているデータを削除する.
     *
     * @param EventArgs $event
     *
     * @deprecated Because the administrator can not delete a category when a category has a product, this event is no longer needed.
     */
    public function onAdminProductCategoryDeleteComplete(EventArgs $event)
    {
        /** @var Category $Category */
        $Category = $event->getArgument('TargetCategory');

        $this->productPriorityRepository->deleteProductPriorityByCategoryId($Category->getId());
    }
}
