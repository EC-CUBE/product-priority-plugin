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

namespace Plugin\ProductPriority\Repository;

use Doctrine\ORM\QueryBuilder;
use Eccube\Entity\Category;
use Eccube\Entity\Product;
use Eccube\Repository\AbstractRepository;
use Eccube\Util\StringUtil;
use Plugin\ProductPriority\Entity\ProductPriority;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ProductPriorityRepository
 */
class ProductPriorityRepository extends AbstractRepository
{
    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductPriority::class);
    }

    /**
     * カテゴリごとに登録されている並び順の件数を返す.
     *
     * @return array array(カテゴリID => 件数)の連想配列
     */
    public function getPriorityCountGroupByCategory()
    {
        $qb = $this->createQueryBuilder('pp');
        $results = $qb->select('pp.category_id, COUNT(pp.category_id)')
            ->groupBy('pp.category_id')
            ->getQuery()
            ->getScalarResult();

        $array = [];
        foreach ($results as $result) {
            $category_id = (int) current($result);
            $count = (int) next($result);
            $array[$category_id] = $count;
        }

        return $array;
    }

    /**
     * 対象カテゴリの並び順の最大値を返す
     *
     * @param $categoryId
     *
     * @return int
     */
    public function getMaxPriorityByCategoryId($categoryId)
    {
        $qb = $this->createQueryBuilder('pp');
        try {
            $max = $qb->select('COALESCE(MAX(pp.priority), 0) as priority_max')
                ->where('pp.category_id = :categoryId')
                ->setParameter('categoryId', $categoryId)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $max = 0;
        }

        return (int) $max;
    }

    /**
     * 並び順をカテゴリで検索する.
     *
     * @param Category|null $Category nullを指定した場合, 全ての商品(カテゴリID:0)の並び順を検索する
     *
     * @return array 以下の連想配列を要素とした配列を返す
     *               array(
     *               'product_id' => xxx,
     *               'category_id' => xxx,
     *               'priority' => xxx,
     *               'product_name' => xxx
     *               );
     */
    public function getPrioritiesByCategoryAsArray(Category $Category = null)
    {
        $categoryId = is_null($Category)
            ? ProductPriority::CATEGORY_ID_ALL_PRODUCT
            : $Category->getId();

        $qb = $this->createQueryBuilder('pp');

        $ProductPriorities = $qb
            ->select(['pp.product_id', 'pp.category_id', 'pp.priority', 'p.name AS product_name'])
            ->innerJoin('Eccube\Entity\Product', 'p', 'WITH', 'pp.product_id = p.id')
            ->where('pp.category_id = :categoryId')
            ->orderBy('pp.priority', 'DESC')
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getArrayResult();

        return $ProductPriorities;
    }

    /**
     * 商品検索を行うクエリビルダを返す.
     * 並び順に登録されている商品は除外される.
     *
     * @param null          $search   商品ID/商品コード/商品名
     * @param Category|null $Category nullを指定した場合, 全ての商品(カテゴリID:0)の並び順を検索する
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getProductQueryBuilder($search = null, Category $Category = null)
    {
        $categoryId = is_null($Category)
            ? ProductPriority::CATEGORY_ID_ALL_PRODUCT
            : $Category->getId();

        $excludedIds = array_map(
            'current',
            $this->createQueryBuilder('pp')
                ->select('pp.product_id')
                ->where('pp.category_id = :category_id')
                ->setParameter('category_id', $categoryId)
                ->getQuery()
                ->getArrayResult()
        );

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select(['p, pc'])
            ->from('Eccube\Entity\Product', 'p')
            ->innerJoin('p.ProductClasses', 'pc');

        // ProductPriorityに登録されている商品は取得しない
        if (count($excludedIds) > 0) {
            $qb
                ->where($qb->expr()->notIn('p.id', ':ids'))
                ->setParameter('ids', $excludedIds);
        }

        // カテゴリ
        if (!is_null($Category)) {
            $qb
                ->innerJoin('p.ProductCategories', 'pct')
                ->innerJoin('pct.Category', 'c')
                ->andWhere('pct.Category = :Categoriy')
                ->setParameter('Categoriy', $Category);
        }

        // 商品ID, 商品名, 商品コード
        if (StringUtil::isNotBlank($search)) {
            $id = preg_match('/^\d+$/', $search) ? $search : null;
            $qb
                ->andWhere('p.id = :id OR p.name LIKE :name_or_code OR pc.code LIKE :name_or_code')
                ->setParameter('id', $id)
                ->setParameter('name_or_code', '%'.$search.'%');
        }

        $qb->orderBy('p.update_date', 'DESC');

        return $qb;
    }

    /**
     * 対象商品のカテゴリに紐付いていない並び順を削除する
     *
     * @param Product $Product
     */
    public function cleanupProductPriority(Product $Product)
    {
        // "全ての商品"は除く
        $categoryIds = [
            ProductPriority::CATEGORY_ID_ALL_PRODUCT,
        ];

        foreach ($Product->getProductCategories() as $ProductCategory) {
            $categoryIds[] = $ProductCategory->getCategory()->getId();
        }

        $qb = $this->createQueryBuilder('pp');

        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('pp.product_id', ':productId'),
                $qb->expr()->notIn('pp.category_id', ':categoryIds')
            )
        )->setParameters(
            [
                'productId' => $Product->getId(),
                'categoryIds' => $categoryIds,
            ]
        );

        $ProductPriorities = $qb->getQuery()->getResult();

        foreach ($ProductPriorities as $ProductPriority) {
            $this->getEntityManager()->remove($ProductPriority);
            $this->getEntityManager()->flush($ProductPriority);
        }
    }

    /**
     * おすすめ順ソートのクエリを構築する
     *
     * @param QueryBuilder $qb
     * @param Category|null $Category
     */
    public function buildSortQuery(QueryBuilder $qb, Category $Category = null)
    {
        // カテゴリ未選択(全ての商品)かどうか
        $isAll = is_null($Category);

        // カテゴリの最大値を取得.
        $categoryQb = $this->getEntityManager()->getRepository('Eccube\Entity\Category')->createQueryBuilder('c');
        try {
            $max = $categoryQb->select('MAX(c.sort_no) + 1')
                ->getQuery()
                ->setMaxResults(1)
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $max = 1;
        }

        /*
         * c.sort_no * 2147483648 + pp.priority については以下を参照
         *
         * @link https://github.com/EC-CUBE/eccube-2_13/blob/master/data/class/pages/products/LC_Page_Products_List.php#L234
         * @link http://xoops.ec-cube.net/modules/newbb/viewtopic.php?viewmode=thread&topic_id=11871&forum=10&post_id=54832#forumpost54832
         *
         * 全商品の並び順はpp.category_id = 0で設定されているため, c.sort_noの最大値+1でsort_priorityを計算する
         */
        if ($isAll) {
            $select = "COALESCE(
                MAX(
                    CASE
                        WHEN pp.category_id = 0 THEN $max * 2147483648 + pp.priority
                        ELSE c.sort_no * 2147483648 + pp.priority
                    END
                ),
             0) AS HIDDEN sort_priority";
            $join = '(pct.product_id = pp.product_id AND pct.category_id = pp.category_id) OR (pct.product_id = pp.product_id AND pp.category_id = 0)';
        } else {
            $select = 'COALESCE(MAX(c.sort_no * 2147483648 + pp.priority), 0) AS HIDDEN sort_priority';
            $join = '(pct.product_id = pp.product_id AND pct.category_id = pp.category_id)';
        }

        $qb
            ->addSelect($select)
            ->leftJoin(
                'Plugin\ProductPriority\Entity\ProductPriority',
                'pp',
                'WITH',
                $join
            )
            ->groupBy('p')
            ->orderBy('sort_priority', 'DESC')
            ->addOrderBy('p.update_date', 'DESC')
            ->addOrderBy('p.id', 'DESC');

        // 在庫無し商品が含まれる場合, dtb_product_classをjoinないと在庫無し商品の制御が行えない.
        // dtb_product_classがjoinされているかどうかを確認し, joinされていなければjoinする.
        $joins = $qb->getDQLPart('join');
        $joinProductClass = false;
        foreach ($joins['p'] as $join) {
            /** @var \Doctrine\ORM\Query\Expr\Join $join */
            if ($join->getJoin() === 'p.ProductClasses') {
                $joinProductClass = true;
                break;
            }
        }

        if (!$joinProductClass) {
            $qb->innerJoin('p.ProductClasses', 'pc');
        }
    }

    /**
     * 対象の商品の並び順を削除する.
     *
     * @param $productId
     */
    public function deleteProductPriorityByProductId($productId)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->delete()
            ->where($qb->expr()->eq('pp.product_id', ':productId'))
            ->setParameter('productId', $productId)
            ->getQuery()
            ->execute();
    }

    /**
     * 対象のカテゴリの並び順を削除する.
     *
     * @param $categoryId
     */
    public function deleteProductPriorityByCategoryId($categoryId)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb
            ->delete()
            ->where($qb->expr()->eq('pp.category_id', ':categoryId'))
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->execute();
    }

    /**
     * ProductCategoryの件数を取得する
     *
     * @param $productId
     * @param $categoryId
     *
     * @return int
     */
    public function countProductCategory($productId, $categoryId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        try {
            $count = $qb->select('COUNT(pc)')
                ->from('Eccube\Entity\ProductCategory', 'pc')
                ->where($qb->expr()->eq('pc.product_id', ':productId'))
                ->andWhere($qb->expr()->eq('pc.category_id', ':categoryId'))
                ->setParameter('productId', $productId)
                ->setParameter('categoryId', $categoryId)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            $count = 0;
        }

        return (int) $count;
    }
}
