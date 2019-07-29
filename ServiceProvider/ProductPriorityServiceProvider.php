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

namespace Plugin\ProductPriority\ServiceProvider;

use Eccube\Common\Constant;
use Plugin\ProductPriority\Form\Type\CategoryType;
use Plugin\ProductPriority\Form\Type\ConfigType;
use Plugin\ProductPriority\Form\Type\SearchType;
use Silex\Application;
use Silex\ServiceProviderInterface;

class ProductPriorityServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        /*
         * Routing
         */
        // http, https判定
        $admin = $app['controllers_factory'];
        if ($app['config']['force_ssl'] == Constant::ENABLED) {
            $admin->requireHttps();
        }

        // 設定画面
        $admin
            ->match(
                '/product/priority/config',
                'Plugin\ProductPriority\Controller\ConfigController::index'
            )->bind('plugin_ProductPriority_config');

        // おすすめ順設定一覧
        $admin
            ->match(
                '/product/priority',
                'Plugin\ProductPriority\Controller\ProductPriorityController::index'
            )->bind('admin_product_priority');

        // おすすめ順設定一覧：カテゴリ指定
        $admin
            ->match(
                '/product/priority/{categoryId}',
                'Plugin\ProductPriority\Controller\ProductPriorityController::index'
            )->assert(
                'categoryId',
                '\d+'
            )->bind('admin_product_priority_edit');

        // おすすめ順の変更
        $admin
            ->post(
                '/product/priority/move/{categoryId}',
                'Plugin\ProductPriority\Controller\ProductPriorityController::move'
            )->assert(
                'categoryId',
                '\d+'
            )->bind('admin_product_priority_move');

        // おすすめ順の一括削除
        $admin
            ->post(
                '/product/priority/delete/{categoryId}',
                'Plugin\ProductPriority\Controller\ProductPriorityController::delete'
            )->assert(
                'categoryId',
                '\d+'
            )->bind('admin_product_priority_delete');

        // モーダル：商品検索
        $admin
            ->match(
                '/product/priority/search',
                'Plugin\ProductPriority\Controller\ProductPriorityController::search'
            )->bind('admin_product_priority_search');

        // モーダル：商品登録
        $admin
            ->post(
                '/product/priority/register/{categoryId}',
                'Plugin\ProductPriority\Controller\ProductPriorityController::register'
            )->assert(
                'categoryId',
                '\d+'
            )->bind('admin_product_priority_register');

        // 管理画面のルーティングにmount
        $app->mount('/'.trim($app['config']['admin_route'], '/').'/', $admin);

        /*
         * Form Types
         */
        $app['form.types'] = $app->share(
            $app->extend(
                'form.types',
                function ($types) use ($app) {
                    $types[] = new CategoryType($app);
                    $types[] = new ConfigType();
                    $types[] = new SearchType();

                    return $types;
                }
            )
        );

        /*
         * Repository
         */
        $app['eccube.plugin.product_priority.repository.product_priority'] = $app->share(
            function () use ($app) {
                $repository = $app['orm.em']->getRepository('Plugin\ProductPriority\Entity\ProductPriority');
                $repository->setApplication($app);

                return $repository;
            }
        );
        $app['eccube.plugin.product_priority.repository.config'] = $app->share(
            function () use ($app) {
                return $app['orm.em']->getRepository('Plugin\ProductPriority\Entity\Config');
            }
        );
        /*
         * Navi
         */
        $app['config'] = $app->share(
            $app->extend(
                'config',
                function ($config) {
                    $addNavi['id'] = 'admin_product_priority';
                    $addNavi['name'] = '商品おすすめ順登録';
                    $addNavi['url'] = 'admin_product_priority';
                    $nav = $config['nav'];
                    foreach ($nav as $key => $val) {
                        if ('product' == $val['id']) {
                            $nav[$key]['child'][] = $addNavi;
                        }
                    }
                    $config['nav'] = $nav;

                    return $config;
                }
            )
        );
    }

    public function boot(Application $app)
    {
    }
}
