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

namespace Plugin\ProductPriority\Controller;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Plugin\ProductPriority\Constant;
use Plugin\ProductPriority\Entity\Config;
use Plugin\ProductPriority\Entity\ProductPriority;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProductPriorityController extends AbstractController
{
    /**
     * 商品並び順の一覧表示.
     *
     * @param Application $app
     * @param Request     $request
     * @param null        $categoryId null: 全商品, 1～: カテゴリ表示
     *
     * @return Response
     */
    public function index(Application $app, Request $request, $categoryId = null)
    {
        // カテゴリの取得
        $Category = is_null($categoryId)
            ? null
            : $app['eccube.repository.category']->find($categoryId);

        // 商品並び順の取得
        $Priorities = $app['eccube.plugin.product_priority.repository.product_priority']
            ->getPrioritiesByCategoryAsArray($Category);

        // カテゴリ一覧プルダウンForm生成
        $builder = $app['form.factory']
            ->createBuilder(
                'admin_product_priority_category',
                array(
                    'category' => $Category,
                )
            );
        $form = $builder->getForm();

        // モーダルの商品検索Form生成
        $builder = $app['form.factory']
            ->createBuilder(
                'admin_product_priority_search',
                array(
                    'category_name' => is_null($Category) ? '全ての商品' : $Category->getName(),
                )
            );
        $searchProductModalForm = $builder->getForm();

        return $app->render(
            'ProductPriority/Resource/template/admin/index.twig',
            array(
                'form' => $form->createView(),
                'searchProductModalForm' => $searchProductModalForm->createView(),
                'Priorities' => $Priorities,
                'categoryId' => is_null($categoryId) ? Constant::CATEGORY_ID_ALL_PRODUCT : $categoryId,
                'Config' => $app['eccube.plugin.product_priority.repository.config']->find(Config::ID)
            )
        );
    }

    /**
     * 商品並び順の並び替えを行う.
     *
     * @param Application $app
     * @param Request     $request
     * @param $categoryId
     *
     * @return bool
     */
    public function move(Application $app, Request $request, $categoryId)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $ranks = $request->request->all();

        foreach ($ranks as $productId => $rank) {
            $Priority = $app['eccube.plugin.product_priority.repository.product_priority']
                ->find(array('product_id' => $productId, 'category_id' => $categoryId));
            $Priority->setPriority($rank);
            $app['orm.em']->flush($Priority);
        }

        return true;
    }

    /**
     * 商品並び順の一括削除.
     *
     * @param Application $app
     * @param Request     $request
     * @param $categoryId
     *
     * @return bool
     */
    public function delete(Application $app, Request $request, $categoryId)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $productIds = $request->request->all();

        foreach ($productIds as $productId) {
            $Priority = $app['eccube.plugin.product_priority.repository.product_priority']
                ->find(array('product_id' => $productId, 'category_id' => $categoryId));
            $app['orm.em']->remove($Priority);
            $app['orm.em']->flush($Priority);
        }

        // 並び順の振り直しを行う.
        $Priorities = $app['eccube.plugin.product_priority.repository.product_priority']
            ->findBy(array('category_id' => $categoryId), array('priority' => 'ASC'));

        $i = 1;
        foreach ($Priorities as $Priority) {
            $Priority->setPriority($i++);
            $app['orm.em']->flush($Priority);
        }

        $app->addSuccess('admin.delete.complete', 'admin');

        return true;
    }

    /**
     * モーダル：商品検索.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function search(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $search = $request->get('search');

        $Category = null;

        if ($categoryId = $request->get('category_id')) {
            $Category = $app['eccube.repository.category']->find($categoryId);
        }

        $qb = $app['eccube.plugin.product_priority.repository.product_priority']
            ->getProductQueryBuilder($search, $Category);

        $pagination = $app['paginator']()->paginate(
            $qb,
            $request->get('page_no', 1),
            $app['config']['default_page_count'],
            array('wrap-queries' => true)
        );

        return $app->render(
            'ProductPriority/Resource/template/admin/search_product.twig',
            array(
                'pagination' => $pagination,
            )
        );
    }

    /**
     * モーダル：商品並び順の登録.
     *
     * @param Application $app
     * @param Request     $request
     * @param $categoryId
     *
     * @return bool
     */
    public function register(Application $app, Request $request, $categoryId)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $productIds = $request->get('productIds');

        foreach ($productIds as $productId) {
            $count = $app['eccube.plugin.product_priority.repository.product_priority']
                ->countProductCategory($productId, $categoryId);

            // 別タブで商品やカテゴリが削除されているような場合は登録をスキップ.
            if ($count < 0) {
                continue;
            }

            $ProductPriority = new ProductPriority();
            $ProductPriority->setProductId($productId);
            $ProductPriority->setCategoryId($categoryId);

            $max = $app['eccube.plugin.product_priority.repository.product_priority']
                ->getMaxPriorityByCategoryId($categoryId);

            $ProductPriority->setPriority($max + 1);

            $app['orm.em']->persist($ProductPriority);
            $app['orm.em']->flush($ProductPriority);
        }

        $app->addSuccess('admin.register.complete', 'admin');

        return true;
    }
}
