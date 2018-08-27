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

namespace Plugin\ProductPriority\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\Master\ProductListOrderByRepository;
use Knp\Component\Pager\Paginator;
use Plugin\ProductPriority\Entity\Config;
use Plugin\ProductPriority\Entity\ProductPriority;
use Plugin\ProductPriority\Form\Type\CategoryType;
use Plugin\ProductPriority\Form\Type\SearchType;
use Plugin\ProductPriority\Repository\ConfigRepository;
use Plugin\ProductPriority\Repository\ProductPriorityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProductPriorityController
 */
class ProductPriorityController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var ProductPriorityRepository
     */
    private $productPriorityRepository;

    /**
     * ProductPriorityController constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param ConfigRepository $configRepository
     * @param ProductPriorityRepository $productPriorityRepository
     */
    public function __construct(CategoryRepository $categoryRepository, ConfigRepository $configRepository, ProductPriorityRepository $productPriorityRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->configRepository = $configRepository;
        $this->productPriorityRepository = $productPriorityRepository;
    }

    /**
     * 商品並び順の一覧表示.
     *
     * @param Request     $request
     * @param null        $categoryId null: 全商品, 1～: カテゴリ表示
     *
     * @return array
     * @Route("/%eccube_admin_route%/plugin/ProductPriority", name="admin_product_priority")
     * @Route("/%eccube_admin_route%/plugin/ProductPriority/{categoryId}", name="admin_product_priority_edit", requirements={"categoryId"= "\d+"})
     * @Template("@ProductPriority/admin/index.twig")
     */
    public function index(Request $request, $categoryId = null)
    {
        // カテゴリの取得
        $Category = is_null($categoryId)
            ? null
            : $this->categoryRepository->find($categoryId);

        // 商品並び順の取得
        $Priorities = $this->productPriorityRepository
            ->getPrioritiesByCategoryAsArray($Category);

        // カテゴリ一覧プルダウンForm生成
        $builder = $this->formFactory
            ->createBuilder(
                CategoryType::class,
                [
                    'category' => $Category,
                ]
            );
        $form = $builder->getForm();

        // モーダルの商品検索Form生成
        $builder = $this->formFactory
            ->createBuilder(
                SearchType::class,
                [
                    'category_name' => is_null($Category) ? trans('product_priority.admin.search.category_name_default') : $Category->getName(),
                ]
            );
        $searchProductModalForm = $builder->getForm();

        /** @var Config $Config */
        $Config = $this->configRepository->find(Config::ID);
        $orderNo = 0;
        if ($Config) {
            $orderNo = $this->container->get(ProductListOrderByRepository::class)->find($Config->getOrderById())->getSortNo();
        }

        return [
            'form' => $form->createView(),
            'searchProductModalForm' => $searchProductModalForm->createView(),
            'Priorities' => $Priorities,
            'categoryId' => is_null($categoryId) ? ProductPriority::CATEGORY_ID_ALL_PRODUCT : $categoryId,
            'order_no' => $orderNo,
        ];
    }

    /**
     * 商品並び順の並び替えを行う.
     *
     * @param Request     $request
     * @param $categoryId
     *
     * @return Response
     * @Route("/%eccube_admin_route%/plugin/ProductPriority/move/{categoryId}", name="admin_product_priority_move", requirements={"categoryId"= "\d+"}, methods={"POST"})
     */
    public function move(Request $request, $categoryId)
    {
        $this->isTokenValid();
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $ranks = $request->request->all();

        foreach ($ranks as $productId => $rank) {
            $Priority = $this->productPriorityRepository
                ->find(['product_id' => $productId, 'category_id' => $categoryId]);
            $Priority->setPriority($rank);
            $this->entityManager->flush($Priority);
        }

        return new Response('OK');
    }

    /**
     * 商品並び順の一括削除.
     *
     * @param Request     $request
     * @param $categoryId
     *
     * @return Response
     * @Route("/%eccube_admin_route%/plugin/ProductPriority/delete/{categoryId}", name="admin_product_priority_delete", requirements={"categoryId"= "\d+"}, methods={"POST"})
     */
    public function delete(Request $request, $categoryId)
    {
        $this->isTokenValid();
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $productIds = $request->request->all();

        foreach ($productIds as $productId) {
            $Priority = $this->productPriorityRepository
                ->find(['product_id' => $productId, 'category_id' => $categoryId]);
            $this->entityManager->remove($Priority);
            $this->entityManager->flush($Priority);
        }

        // 並び順の振り直しを行う.
        /** @var ProductPriority[] $Priorities */
        $Priorities = $this->productPriorityRepository
            ->findBy(['category_id' => $categoryId], ['priority' => 'ASC']);

        $i = 1;
        foreach ($Priorities as $Priority) {
            $Priority->setPriority($i++);
            $this->entityManager->flush($Priority);
        }

        $this->addSuccess('product_review.admin.delete.complete', 'admin');

        return new Response('OK');
    }

    /**
     * モーダル：商品検索.
     *
     * @param Request     $request
     * @param int $page_no
     * @param Paginator $paginator
     *
     * @return array
     * @Route("/%eccube_admin_route%/plugin/ProductPriority/search", name="admin_product_priority_search")
     * @Route("/%eccube_admin_route%/plugin/ProductPriority/search/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_priority_search_page")
     * @Template("@ProductPriority/admin/search_product.twig")
     */
    public function search(Request $request, $page_no = 1, Paginator $paginator)
    {
        $this->isTokenValid();
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $search = $request->get('search');

        $Category = null;

        if ($categoryId = $request->get('category_id')) {
            $Category = $this->categoryRepository->find($categoryId);
        }

        $qb = $this->productPriorityRepository
            ->getProductQueryBuilder($search, $Category);

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $this->eccubeConfig['eccube_default_page_count'],
            ['wrap-queries' => true]
        );

        return
            [
                'pagination' => $pagination,
        ];
    }

    /**
     * モーダル：商品並び順の登録.
     *
     * @param Request     $request
     * @param $categoryId
     *
     * @return Response
     * @Route("/%eccube_admin_route%/plugin/ProductPriority/register/{categoryId}", name="admin_product_priority_register", requirements={"categoryId"= "\d+"}, methods={"POST"})
     */
    public function register(Request $request, $categoryId)
    {
        $this->isTokenValid();
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $productIds = $request->get('productIds');

        foreach ($productIds as $productId) {
            $count = $this->productPriorityRepository
                ->countProductCategory($productId, $categoryId);

            // 別タブで商品やカテゴリが削除されているような場合は登録をスキップ.
            if ($count < 0) {
                continue;
            }

            $ProductPriority = new ProductPriority();
            $ProductPriority->setProductId($productId);
            $ProductPriority->setCategoryId($categoryId);

            $max = $this->productPriorityRepository
                ->getMaxPriorityByCategoryId($categoryId);

            $ProductPriority->setPriority($max + 1);

            $this->entityManager->persist($ProductPriority);
            $this->entityManager->flush($ProductPriority);
        }

        $this->addSuccess('admin.common.save_complete', 'admin');

        return new Response('OK');
    }
}
